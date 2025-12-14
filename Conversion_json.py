import json
from netCDF4 import Dataset

def netcdf_to_latlon_json(nc_path, json_path):
    ds = Dataset(nc_path, "r")

    lats = ds.variables["latitude"][:].tolist()
    lons = ds.variables["longitude"][:].tolist()

    points = []

    for lat in lats:
        for lon in lons:
            points.append({
                "latitude": float(lat),
                "longitude": float(lon)
            })

    ds.close()

    with open(json_path, "w", encoding="utf-8") as f:
        json.dump(points, f, indent=2)


# nombre de décimales conservées pour comparer les coordonnées
PRECISION = 6

def append_netcdf_to_json_dedup(nc_path, json_path):
    # 1️⃣ Charger le JSON existant
    with open(json_path, "r", encoding="utf-8") as f:
        points = json.load(f)

    # 2️⃣ Index des points existants (set pour dédoublonnage rapide)
    seen = set(
        (round(p["latitude"], PRECISION), round(p["longitude"], PRECISION))
        for p in points
    )

    # 3️⃣ Charger le NetCDF
    ds = Dataset(nc_path, "r")
    lats = ds.variables["latitude"][:]
    lons = ds.variables["longitude"][:]

    added = 0

    # 4️⃣ Ajouter uniquement les nouveaux points
    for lat in lats:
        for lon in lons:
            key = (round(float(lat), PRECISION), round(float(lon), PRECISION))
            if key not in seen:
                points.append({
                    "latitude": key[0],
                    "longitude": key[1]
                })
                seen.add(key)
                added += 1

    ds.close()

    # 5️⃣ Réécrire le JSON
    with open(json_path, "w", encoding="utf-8") as f:
        json.dump(points, f, indent=2)

    print(f"{added} nouveaux points ajoutés (doublons ignorés).")



if __name__ == "__main__":
    netcdf_to_latlon_json(
        "cmems_mod_glo_phy_anfc_0.083deg_PT1H-m_thetao_142.50E-266.67E_60.25S-65.58N_0.49m_2025-12-21.nc",
        "points_latlon.json"
    )
    append_netcdf_to_json_dedup(
        "cmems_mod_glo_phy_anfc_0.083deg_PT1H-m_thetao_106.58E-142.58E_8.75S-58.42N_0.49m_2025-12-21.nc",
        "points_latlon.json"
    )
    append_netcdf_to_json_dedup(
        "cmems_mod_glo_phy_anfc_0.083deg_PT1H-m_thetao_93.08W-69.92W_59.75S-10.33N_0.49m_2025-12-21.nc",
        "points_latlon.json"
    )
