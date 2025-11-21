import os
import copernicusmarine

if os.path.exists("cmems_mod_glo_bgc-car_anfc_0.25deg_P1M-m_ph_180.00W-179.75E_80.00S-90.00N_0.49m_2023-10-01-2025-10-01.nc"):
    os.remove("cmems_mod_glo_bgc-car_anfc_0.25deg_P1M-m_ph_180.00W-179.75E_80.00S-90.00N_0.49m_2023-10-01-2025-10-01.nc")
    
if os.path.exists("cmems_mod_glo_phy-so_anfc_0.083deg_P1M-m_so_180.00W-179.92E_80.00S-90.00N_0.49m_2023-06-01-2025-10-01.nc"):
    os.remove("cmems_mod_glo_phy-so_anfc_0.083deg_P1M-m_so_180.00W-179.92E_80.00S-90.00N_0.49m_2023-06-01-2025-10-01.nc")

if os.path.exists("cmems_mod_glo_phy-thetao_anfc_0.083deg_P1M-m_thetao_180.00W-179.92E_80.00S-90.00N_0.49m_2023-06-01-2025-10-01.nc"):
    os.remove("cmems_mod_glo_phy-thetao_anfc_0.083deg_P1M-m_thetao_180.00W-179.92E_80.00S-90.00N_0.49m_2023-06-01-2025-10-01.nc")

copernicusmarine.subset(
  dataset_id="cmems_mod_glo_bgc-car_anfc_0.25deg_P1M-m",
  variables=["ph"],
  minimum_longitude=-180,
  maximum_longitude=179.75,
  minimum_latitude=-80,
  maximum_latitude=90,
  start_datetime="2024-10-01T00:00:00",
  end_datetime="2025-10-01T00:00:00",
  minimum_depth=0.4940253794193268,
  maximum_depth=0.4940253794193268,
)

copernicusmarine.subset(
  dataset_id="cmems_mod_glo_phy-so_anfc_0.083deg_P1M-m",
  variables=["so"],
  minimum_longitude=-180,
  maximum_longitude=179.91668701171875,
  minimum_latitude=-80,
  maximum_latitude=90,
  start_datetime="2024-10-01T00:00:00",
  end_datetime="2025-10-01T00:00:00",
  minimum_depth=0.49402499198913574,
  maximum_depth=0.49402499198913574,
)

copernicusmarine.subset(
  dataset_id="cmems_mod_glo_phy-thetao_anfc_0.083deg_P1M-m",
  variables=["thetao"],
  minimum_longitude=-180,
  maximum_longitude=179.91668701171875,
  minimum_latitude=-80,
  maximum_latitude=90,
  start_datetime="2024-10-01T00:00:00",
  end_datetime="2025-10-01T00:00:00",
  minimum_depth=0.49402499198913574,
  maximum_depth=0.49402499198913574,
)