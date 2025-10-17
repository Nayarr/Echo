# Import modules
import copernicusmarine

# Load dataframe
request_dataframe = copernicusmarine.read_dataframe(
    dataset_id="cmems_mod_glo_bgc-car_anfc_0.25deg_P1D-m",
    variables=["ph"],
    minimum_longitude=-180,
    maximum_longitude=179.75,
    minimum_latitude=-80,
    maximum_latitude=90,
    start_datetime="2025-09-17T00:00:00",
    end_datetime="2025-09-26T00:00:00",
    minimum_depth=0.4940253794193268,
    maximum_depth=0.4940253794193268,
)

# Remettre les dimensions comme colonnes
df_reset = request_dataframe.reset_index()

# Garder seulement les lignes avec une valeur de pH
df_valid = df_reset.dropna(subset=["ph"])

# Afficher un résumé
print(f"Nombre de points valides trouvés : {len(df_valid)}")

# Afficher un aperçu des points valides (ex. 20 premiers)
print(df_valid[["time", "latitude", "longitude", "depth", "ph"]].tail(20))