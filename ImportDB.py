import mysql.connector
import json
import os

# --------------------------------------------------------
# CONFIG MYSQL (MAMP par défaut)
# --------------------------------------------------------
DB_CONFIG = {
    "host": "localhost",
    "port": 8889,
    "user": "root",
    "password": "root",
    "database": "Echo",  # la BD doit exister avant
}

# Noms des fichiers JSON
PH_JSON = "ph.json"
SO_JSON = "so.json"
THETAO_JSON = "thetao.json"


# --------------------------------------------------------
# UTILITAIRE : lecture de gros JSON en streaming
# (format : [ { ... }, { ... }, ... ])
# --------------------------------------------------------
def stream_json_objects(filename):
    """
    Lit un fichier JSON contenant un tableau d'objets
    sans le charger entièrement en mémoire.
    Yield un dict Python à la fois.
    """
    with open(filename, "rb") as f:
        buffer = b""
        inside = False
        brace_count = 0

        while True:
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
                    try:
                        obj = json.loads(buffer.decode("utf-8"))
                        yield obj
                    except Exception:
                        pass
                    inside = False


def coord_key(lat, lon, ndigits=5):
    """Clé normalisée pour un couple (lat, lon)."""
    if lat is None or lon is None:
        return None
    return (round(float(lat), ndigits), round(float(lon), ndigits))


# --------------------------------------------------------
# CREATION / RESET DES TABLES
# --------------------------------------------------------
def reset_tables(cursor):
    print("Suppression des anciennes tables...")
    cursor.execute("SET FOREIGN_KEY_CHECKS = 0")

    for table in ["thetao_valeurs", "so_valeurs", "ph_valeurs",
                  "coord_so_thetao", "coord_ph"]:
        cursor.execute(f"DROP TABLE IF EXISTS {table}")

    cursor.execute("SET FOREIGN_KEY_CHECKS = 1")
    print("Tables supprimées.")

    print("Création des nouvelles tables...")

    cursor.execute("""
        CREATE TABLE coord_ph (
            id_coord INT AUTO_INCREMENT PRIMARY KEY,
            latitude DOUBLE NOT NULL,
            longitude DOUBLE NOT NULL,
            UNIQUE KEY uniq_coord_ph (latitude, longitude)
        ) ENGINE=InnoDB;
    """)

    cursor.execute("""
        CREATE TABLE ph_valeurs (
            id_valeur INT AUTO_INCREMENT PRIMARY KEY,
            id_coord INT NOT NULL,
            time DATETIME NOT NULL,
            ph_valeur FLOAT,
            FOREIGN KEY (id_coord) REFERENCES coord_ph(id_coord)
                ON DELETE CASCADE
        ) ENGINE=InnoDB;
    """)

    cursor.execute("""
        CREATE TABLE coord_so_thetao (
            id_coord INT AUTO_INCREMENT PRIMARY KEY,
            latitude DOUBLE NOT NULL,
            longitude DOUBLE NOT NULL,
            UNIQUE KEY uniq_coord_so_thetao (latitude, longitude)
        ) ENGINE=InnoDB;
    """)

    cursor.execute("""
        CREATE TABLE so_valeurs (
            id_valeur INT AUTO_INCREMENT PRIMARY KEY,
            id_coord INT NOT NULL,
            time DATETIME NOT NULL,
            so_valeur FLOAT,
            FOREIGN KEY (id_coord) REFERENCES coord_so_thetao(id_coord)
                ON DELETE CASCADE
        ) ENGINE=InnoDB;
    """)

    cursor.execute("""
        CREATE TABLE thetao_valeurs (
            id_valeur INT AUTO_INCREMENT PRIMARY KEY,
            id_coord INT NOT NULL,
            time DATETIME NOT NULL,
            thetao_valeur FLOAT,
            FOREIGN KEY (id_coord) REFERENCES coord_so_thetao(id_coord)
                ON DELETE CASCADE
        ) ENGINE=InnoDB;
    """)

    print("Tables créées.")


# --------------------------------------------------------
# INSERTION PH : coord_ph + ph_valeurs
# --------------------------------------------------------
def insert_ph_data(cursor, ph_json):
    if not os.path.exists(ph_json):
        print(f"[PH] Fichier introuvable : {ph_json}")
        return

    print(f"[PH] Insertion depuis {ph_json} ...")

    coord_map = {}  # (lat, lon) -> id_coord
    rows_buffer = []
    buffer_size = 5000
    total = 0

    for obj in stream_json_objects(ph_json):
        lat = obj.get("latitude")
        lon = obj.get("longitude")
        time = obj.get("time")
        value = obj.get("ph")

        k = coord_key(lat, lon)
        if k is None or time is None:
            continue

        if k not in coord_map:
            cursor.execute(
                "INSERT INTO coord_ph (latitude, longitude) VALUES (%s, %s)",
                (k[0], k[1])
            )
            coord_id = cursor.lastrowid
            coord_map[k] = coord_id
        else:
            coord_id = coord_map[k]

        rows_buffer.append((coord_id, time, float(value) if value is not None else None))
        total += 1

        if len(rows_buffer) >= buffer_size:
            cursor.executemany(
                "INSERT INTO ph_valeurs (id_coord, time, ph_valeur) VALUES (%s, %s, %s)",
                rows_buffer
            )
            rows_buffer.clear()
            print(f"[PH] {total} lignes insérées...")

    if rows_buffer:
        cursor.executemany(
            "INSERT INTO ph_valeurs (id_coord, time, ph_valeur) VALUES (%s, %s, %s)",
            rows_buffer
        )

    print(f"[PH] Insertion terminée. Total lignes : {total}")


# --------------------------------------------------------
# INSERTION SO : coord_so_thetao + so_valeurs
# --------------------------------------------------------
def insert_so_data(cursor, so_json):
    if not os.path.exists(so_json):
        print(f"[SO] Fichier introuvable : {so_json}")
        return

    print(f"[SO] Insertion depuis {so_json} ...")

    coord_map = {}  # (lat, lon) -> id_coord
    rows_buffer = []
    buffer_size = 5000
    total = 0

    for obj in stream_json_objects(so_json):
        lat = obj.get("latitude")
        lon = obj.get("longitude")
        time = obj.get("time")
        value = obj.get("so")

        k = coord_key(lat, lon)
        if k is None or time is None:
            continue

        if k not in coord_map:
            cursor.execute(
                "INSERT INTO coord_so_thetao (latitude, longitude) VALUES (%s, %s)",
                (k[0], k[1])
            )
            coord_id = cursor.lastrowid
            coord_map[k] = coord_id
        else:
            coord_id = coord_map[k]

        rows_buffer.append((coord_id, time, float(value) if value is not None else None))
        total += 1

        if len(rows_buffer) >= buffer_size:
            cursor.executemany(
                "INSERT INTO so_valeurs (id_coord, time, so_valeur) VALUES (%s, %s, %s)",
                rows_buffer
            )
            rows_buffer.clear()
            print(f"[SO] {total} lignes insérées...")

    if rows_buffer:
        cursor.executemany(
            "INSERT INTO so_valeurs (id_coord, time, so_valeur) VALUES (%s, %s, %s)",
            rows_buffer
        )

    print(f"[SO] Insertion terminée. Total lignes : {total}")
    return coord_map  # à réutiliser pour thetao


# --------------------------------------------------------
# INSERTION THETAO : thetao_valeurs (en réutilisant coord_so_thetao)
# --------------------------------------------------------
def insert_thetao_data(cursor, thetao_json, coord_map):
    if not os.path.exists(thetao_json):
        print(f"[THETAO] Fichier introuvable : {thetao_json}")
        return

    if coord_map is None:
        print("[THETAO] Pas de grille coord_so_thetao fournie, abandon.")
        return

    print(f"[THETAO] Insertion depuis {thetao_json} ...")

    rows_buffer = []
    buffer_size = 5000
    total = 0

    for obj in stream_json_objects(thetao_json):
        lat = obj.get("latitude")
        lon = obj.get("longitude")
        time = obj.get("time")
        value = obj.get("thetao")

        k = coord_key(lat, lon)
        if k is None or time is None:
            continue

        coord_id = coord_map.get(k)
        if coord_id is None:
            # en théorie ne doit pas arriver si les grilles sont identiques
            cursor.execute(
                "INSERT INTO coord_so_thetao (latitude, longitude) VALUES (%s, %s)",
                (k[0], k[1])
            )
            coord_id = cursor.lastrowid
            coord_map[k] = coord_id

        rows_buffer.append((coord_id, time, float(value) if value is not None else None))
        total += 1

        if len(rows_buffer) >= buffer_size:
            cursor.executemany(
                "INSERT INTO thetao_valeurs (id_coord, time, thetao_valeur) VALUES (%s, %s, %s)",
                rows_buffer
            )
            rows_buffer.clear()
            print(f"[THETAO] {total} lignes insérées...")

    if rows_buffer:
        cursor.executemany(
            "INSERT INTO thetao_valeurs (id_coord, time, thetao_valeur) VALUES (%s, %s, %s)",
            rows_buffer
        )

    print(f"[THETAO] Insertion terminée. Total lignes : {total}")


# --------------------------------------------------------
# MAIN
# --------------------------------------------------------
def main():
    print("Connexion à MySQL...")
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    try:
        reset_tables(cursor)
        conn.commit()

        insert_ph_data(cursor, PH_JSON)
        conn.commit()

        coord_map_so = insert_so_data(cursor, SO_JSON)
        conn.commit()

        insert_thetao_data(cursor, THETAO_JSON, coord_map_so)
        conn.commit()

        print("\n✅ Import terminé avec succès.")
    finally:
        cursor.close()
        conn.close()
        print("Connexion MySQL fermée.")


if __name__ == "__main__":
    main()
