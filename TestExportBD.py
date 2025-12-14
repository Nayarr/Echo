import mysql.connector
import json

DB_CONFIG = {
    "host": "localhost",
    "port": 8889,
    "user": "root",
    "password": "root",
    "database": "Echo",
}

PH_JSON = "ph.json"
SO_JSON = "so.json"
THETAO_JSON = "thetao.json"


# --- STREAM 1 OBJET JSON ---
def stream_first_objects(filename, limit=5):
    with open(filename, "rb") as f:
        buffer = b""
        inside = False
        brace_count = 0
        count = 0

        while count < limit:
            c = f.read(1)
            if not c:
                break

            if c == b"{":
                inside = True
                buffer = b"{"
                brace_count = 1
                continue

            if inside:
                buffer += c

                if c == b"{":
                    brace_count += 1
                elif c == b"}":
                    brace_count -= 1

                if brace_count == 0:
                    obj = json.loads(buffer.decode("utf-8"))
                    yield obj
                    inside = False
                    count += 1


# --- MAIN ---
def main():
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    print("Suppression des tables…")
    cursor.execute("SET FOREIGN_KEY_CHECKS = 0")
    for table in ["thetao_valeurs", "so_valeurs", "ph_valeurs", "coord_so_thetao", "coord_ph"]:
        cursor.execute(f"DROP TABLE IF EXISTS {table}")
    cursor.execute("SET FOREIGN_KEY_CHECKS = 1")

    print("Création des tables…")

    cursor.execute("""
        CREATE TABLE coord_ph (
            id_coord INT AUTO_INCREMENT PRIMARY KEY,
            latitude DOUBLE NOT NULL,
            longitude DOUBLE NOT NULL
        )
    """)

    cursor.execute("""
        CREATE TABLE ph_valeurs (
            id_valeur INT AUTO_INCREMENT PRIMARY KEY,
            id_coord INT NOT NULL,
            time DATETIME,
            ph_valeur FLOAT,
            FOREIGN KEY (id_coord) REFERENCES coord_ph(id_coord)
        )
    """)

    cursor.execute("""
        CREATE TABLE coord_so_thetao (
            id_coord INT AUTO_INCREMENT PRIMARY KEY,
            latitude DOUBLE NOT NULL,
            longitude DOUBLE NOT NULL
        )
    """)

    cursor.execute("""
        CREATE TABLE so_valeurs (
            id_valeur INT AUTO_INCREMENT PRIMARY KEY,
            id_coord INT NOT NULL,
            time DATETIME,
            so_valeur FLOAT,
            FOREIGN KEY (id_coord) REFERENCES coord_so_thetao(id_coord)
        )
    """)

    cursor.execute("""
        CREATE TABLE thetao_valeurs (
            id_valeur INT AUTO_INCREMENT PRIMARY KEY,
            id_coord INT NOT NULL,
            time DATETIME,
            thetao_valeur FLOAT,
            FOREIGN KEY (id_coord) REFERENCES coord_so_thetao(id_coord)
        )
    """)

    conn.commit()
    print("Tables créées ✔")

    # ----------------------------------------------------------
    # INSERTION TEST PH (5 objets)
    # ----------------------------------------------------------
    print("\nInsertion test PH (5 valeurs)…")

    coord_map_ph = {}

    for obj in stream_first_objects(PH_JSON, 5):
        lat = float(obj["latitude"])
        lon = float(obj["longitude"])
        time = obj["time"]
        value = obj["ph"]

        key = (lat, lon)

        if key not in coord_map_ph:
            cursor.execute("INSERT INTO coord_ph (latitude, longitude) VALUES (%s, %s)", (lat, lon))
            coord_map_ph[key] = cursor.lastrowid

        id_coord = coord_map_ph[key]

        cursor.execute(
            "INSERT INTO ph_valeurs (id_coord, time, ph_valeur) VALUES (%s, %s, %s)",
            (id_coord, time, value)
        )

    conn.commit()
    print("✔ 5 valeurs PH insérées")

    # ----------------------------------------------------------
    # INSERTION TEST SO (5 objets)
    # ----------------------------------------------------------
    print("\nInsertion test SO (5 valeurs)…")

    coord_map_so = {}

    for obj in stream_first_objects(SO_JSON, 5):
        lat = float(obj["latitude"])
        lon = float(obj["longitude"])
        time = obj["time"]
        value = obj["so"]

        key = (lat, lon)

        if key not in coord_map_so:
            cursor.execute("INSERT INTO coord_so_thetao (latitude, longitude) VALUES (%s, %s)", (lat, lon))
            coord_map_so[key] = cursor.lastrowid

        id_coord = coord_map_so[key]

        cursor.execute(
            "INSERT INTO so_valeurs (id_coord, time, so_valeur) VALUES (%s, %s, %s)",
            (id_coord, time, value)
        )

    conn.commit()
    print("✔ 5 valeurs SO insérées")

    # ----------------------------------------------------------
    # INSERTION TEST THETAO (5 objets)
    # ----------------------------------------------------------
    print("\nInsertion test THETAO (5 valeurs)…")

    for obj in stream_first_objects(THETAO_JSON, 5):
        lat = float(obj["latitude"])
        lon = float(obj["longitude"])
        time = obj["time"]
        value = obj["thetao"]

        key = (lat, lon)

        # coordonnée doit déjà exister
        if key not in coord_map_so:
            print("⚠ Coordonnée absente, insertion forcée")
            cursor.execute("INSERT INTO coord_so_thetao (latitude, longitude) VALUES (%s, %s)", (lat, lon))
            coord_map_so[key] = cursor.lastrowid

        id_coord = coord_map_so[key]

        cursor.execute(
            "INSERT INTO thetao_valeurs (id_coord, time, thetao_valeur) VALUES (%s, %s, %s)",
            (id_coord, time, value)
        )

    conn.commit()
    print("✔ 5 valeurs THETAO insérées")

    cursor.close()
    conn.close()
    print("\n🏁 Test terminé : 5 valeurs de chaque dataset ont été importées avec succès.")


if __name__ == "__main__":
    main()
