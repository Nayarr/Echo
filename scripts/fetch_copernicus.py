#!/usr/bin/env python3
"""
Récupère une série temporelle pour un point avec copernicusmarine.
Usage exemple:
  python3 scripts/fetch_copernicus.py --lat 34.121 --lon 35.381 \
    --start 1997-09-16 --end 1997-12-31 \
    --dataset cmems_mod_glo_phy_anfc_0.083deg_PT1H-m --variables so,thetao

Sortie: JSON list d'objets {"date":"YYYY-MM-DD","lat":..,"lon":..,"values": {var: val, ...}}

Remarques: nécessite l'installation de la lib `copernicusmarine` et ses dépendances.
"""
import argparse
import json
import sys

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--lat', type=float, required=True)
    parser.add_argument('--lon', type=float, required=True)
    parser.add_argument('--start', required=True)
    parser.add_argument('--end', required=True)
    parser.add_argument('--dataset', required=True)
    parser.add_argument('--variables', required=True, help='comma separated list')
    args = parser.parse_args()

    vars_list = [v.strip() for v in args.variables.split(',') if v.strip()]

    try:
        import copernicusmarine as cm
    except Exception as e:
        print(json.dumps({'error': 'copernicusmarine import failed: %s' % str(e)}))
        sys.exit(1)

    try:
        df = cm.read_dataframe(
            dataset_id = args.dataset,
            minimum_longitude = args.lon,
            maximum_longitude = args.lon,
            minimum_latitude = args.lat,
            maximum_latitude = args.lat,
            variables = vars_list,
            start_datetime = args.start,
            end_datetime = args.end
        )
    except Exception as e:
        print(json.dumps({'error': 'data read failed: %s' % str(e)}))
        sys.exit(1)

    # df expected: datetime index, columns for variables
    try:
        df_reset = df.reset_index()
    except Exception:
        # if df is a Series
        df_reset = df.to_frame().reset_index()

    results = []
    # try to infer time column name
    time_col = None
    for c in df_reset.columns:
        if 'time' in str(c).lower() or 'date' in str(c).lower():
            time_col = c
            break
    if time_col is None:
        # assume first column is time
        time_col = df_reset.columns[0]

    for _, row in df_reset.iterrows():
        dt = row[time_col]
        # format date
        try:
            date_str = str(dt)[:10]
        except Exception:
            date_str = str(dt)

        values = {}
        for v in vars_list:
            # column might be v or ('variable',) depending on df
            val = None
            if v in row.index:
                val = row[v]
            else:
                # try lowercase
                for col in row.index:
                    if str(col).lower() == v.lower():
                        val = row[col]
                        break
            # convert numpy types
            try:
                if hasattr(val, 'item'):
                    val = val.item()
            except Exception:
                pass
            values[v] = None if (val is None or (isinstance(val, float) and (str(val) == 'nan'))) else val

        results.append({'date': date_str, 'lat': args.lat, 'lon': args.lon, 'values': values})

    print(json.dumps(results, ensure_ascii=False))

if __name__ == '__main__':
    main()
