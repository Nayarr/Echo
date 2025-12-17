import mysql.connector
import json
import os

# --------------------------------------------------------
# CONFIG MYSQL (MAMP par défaut)
# --------------------------------------------------------
DB_CONFIG = {
    "host": "localhost",
    "port": 3306,
    "user": "root",
    "password": "",
    "database": "Echo",  # la BD doit exister avant
}

# Noms des fichiers JSON
POINTS = "points_latlon.json"


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
def Index_create(cursor):
    print("Création des index")

    cursor.execute("CREATE INDEX idx_lat_lon ON Points(latitude, longitude);")
    cursor.execute("CREATE INDEX idx_id_point ON Points(id_point);")


    

def reset_tables(cursor):
    print("Suppression des anciennes tables...")

    cursor.execute(f"DROP TABLE IF EXISTS Points")
    
    print("Tables supprimées.")

    print("Création des nouvelles tables...")

    cursor.execute("""
        CREATE TABLE Points (
        id_point INT AUTO_INCREMENT PRIMARY KEY,
        latitude DOUBLE NOT NULL,
        longitude DOUBLE NOT NULL,
        geom POINT NOT NULL,
        UNIQUE KEY uniq_coord (latitude, longitude),
        SPATIAL INDEX idx_geom (geom)
        ) ENGINE=InnoDB;
    """)

    

    print("Tables créées.")


# --------------------------------------------------------
# INSERTION PH : coord_ph + ph_valeurs
# --------------------------------------------------------
def insert_points_data(cursor, points_json):
    if not os.path.exists(points_json):
        print(f"[POINTS] Fichier introuvable : {points_json}")
        return

    print(f"[POINTS] Insertion depuis {points_json} ...")

    buffer = []
    buffer_size = 100000
    total = 0

    for obj in stream_json_objects(points_json):
        lat = obj.get("latitude")
        lon = obj.get("longitude")

        if lat is None or lon is None:
            continue

        lat = float(lat)
        lon = float(lon)

        # ⚠️ ordre IMPORTANT : POINT(longitude, latitude)
        buffer.append((lat, lon, lon, lat))
        total += 1

        if len(buffer) >= buffer_size:
            cursor.executemany(
                """
                INSERT IGNORE INTO Points (latitude, longitude, geom)
                VALUES (%s, %s, POINT(%s, %s))
                """,
                buffer
            )
            buffer.clear()
            print(f"[POINTS] {total} points traités...")

    if buffer:
        cursor.executemany(
            """
            INSERT IGNORE INTO Points (latitude, longitude, geom)
            VALUES (%s, %s, POINT(%s, %s))
            """,
            buffer
        )

    print(f"[POINTS] Insertion terminée. Total points traités : {total}")


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

        insert_points_data(cursor, POINTS)
        conn.commit()

        Index_create(cursor)
        conn.commit()


        print("\n[OK] Import terminé avec succès.")
    finally:
        cursor.close()
        conn.close()
        print("Connexion MySQL fermée.")


if __name__ == "__main__":
    main()
