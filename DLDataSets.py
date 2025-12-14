import os
import copernicusmarine

fichier1 = "cmems_mod_glo_phy_anfc_0.083deg_PT1H-m_thetao_93.08W-69.92W_59.75S-10.33N_0.49m_2025-12-21.nc"
fichier2 = "cmems_mod_glo_phy_anfc_0.083deg_PT1H-m_thetao_106.58E-142.58E_8.75S-58.42N_0.49m_2025-12-21.nc"
fichier3 = "cmems_mod_glo_phy_anfc_0.083deg_PT1H-m_thetao_142.50E-266.67E_60.25S-65.58N_0.49m_2025-12-21.nc"

if os.path.exists(fichier1):
    os.remove(fichier1)
if os.path.exists(fichier2):
    os.remove(fichier2)
if os.path.exists(fichier3):
    os.remove(fichier3)

copernicusmarine.subset(
  dataset_id="cmems_mod_glo_phy_anfc_0.083deg_PT1H-m",
  variables=["thetao"],
  minimum_longitude=142.4880974729829,
  maximum_longitude=266.6721928620632,
  minimum_latitude=-60.31982357735927,
  maximum_latitude=65.59385531296171,
  start_datetime="2025-12-21T23:00:00",
  end_datetime="2025-12-21T23:00:00",
  minimum_depth=0.49402499198913574,
  maximum_depth=0.49402499198913574,
)

copernicusmarine.subset(
  dataset_id="cmems_mod_glo_phy_anfc_0.083deg_PT1H-m",
  variables=["thetao"],
  minimum_longitude=106.57168145214833,
  maximum_longitude=142.60998193218467,
  minimum_latitude=-8.750556987573532,
  maximum_latitude=58.445857449160876,
  start_datetime="2025-12-21T23:00:00",
  end_datetime="2025-12-21T23:00:00",
  minimum_depth=0.49402499198913574,
  maximum_depth=0.49402499198913574,
)

copernicusmarine.subset(
  dataset_id="cmems_mod_glo_phy_anfc_0.083deg_PT1H-m",
  variables=["thetao"],
  minimum_longitude=-93.13296120435672,
  maximum_longitude=-69.85822547766662,
  minimum_latitude=-59.80481600095835,
  maximum_latitude=10.394790142445771,
  start_datetime="2025-12-21T23:00:00",
  end_datetime="2025-12-21T23:00:00",
  minimum_depth=0.49402499198913574,
  maximum_depth=0.49402499198913574,
)