import xarray as xr
import json
import os

# Taille des blocs à traiter
CHUNK_LAT = 60
CHUNK_LON = 60

FILES = {
    "ph": "cmems_mod_glo_bgc-car_anfc_0.25deg_P1M-m_ph_180.00W-179.75E_80.00S-90.00N_0.49m_2024-10-01-2025-10-01.nc",
    "so": "cmems_mod_glo_phy-so_anfc_0.083deg_P1M-m_so_180.00W-179.92E_80.00S-90.00N_0.49m_2024-10-01-2025-10-01.nc",
    "thetao": "cmems_mod_glo_phy-thetao_anfc_0.083deg_P1M-m_thetao_180.00W-179.92E_80.00S-90.00N_0.49m_2024-10-01-2025-10-01.nc"
}


def convert_chunked(var_name, filename):
    print(f"\n=== Traitement de {var_name} ===")

    ds = xr.open_dataset(filename, chunks={"latitude": CHUNK_LAT, "longitude": CHUNK_LON})
    da = ds[var_name]

    json_file = f"{var_name}.json"

    if os.path.exists(json_file):
        os.remove(json_file)

    with open(json_file, "w") as f:
        f.write("[")
        first_record = True

        for t in range(da.sizes["time"]):
            print(f" → Time index {t+1}/{da.sizes['time']}")

            slice_time = da.isel(time=t)

            for lat_start in range(0, slice_time.sizes["latitude"], CHUNK_LAT):
                lat_end = min(lat_start + CHUNK_LAT, slice_time.sizes["latitude"])

                lat_block = slice_time.isel(latitude=slice(lat_start, lat_end))

                for lon_start in range(0, slice_time.sizes["longitude"], CHUNK_LON):
                    lon_end = min(lon_start + CHUNK_LON, slice_time.sizes["longitude"])

                    block = lat_block.isel(longitude=slice(lon_start, lon_end))

                    df = block.to_dataframe().dropna().reset_index()

                    for _, row in df.iterrows():

                        if not first_record:
                            f.write(",")

                        json.dump({
                            "time": str(row["time"]),
                            "latitude": float(row["latitude"]),
                            "longitude": float(row["longitude"]),
                            var_name: float(row[var_name])
                        }, f)

                        first_record = False

        f.write("]")

    print(f"✔ Terminé : {json_file}")


if __name__ == "__main__":
    for var, file in FILES.items():
        convert_chunked(var, file)

    print("\n=== FIN DU TRAITEMENT ===")