# Database initialization

## Building the Initial Database for Spatial Analysis (collab_web)

This document outlines the steps to build the initial SQLite database (`spaces.db`) required for the collab_web application, including the necessary geographical data for spatial analysis.  The Python scripts `crea-changeset-withHeaders.py` and `crea-changeset-withHeaders_JOSM.py` handle the export of data to OSM format, while the PHP backend handles the API and database interactions.

### 1. Database Schema

The database needs two primary tables: `spaces` and `entrances`.  The `schools` table is populated via the frontend.

**Table: `spaces`**

| Column Name        | Data Type | Constraints                     | Description                                          |
|---------------------|------------|---------------------------------|------------------------------------------------------|
| id                  | INTEGER    | PRIMARY KEY, AUTOINCREMENT       | Unique identifier for each space                    |
| name                | TEXT       | NOT NULL                         | Name of the space                                     |
| latitude            | REAL       | NOT NULL                         | Latitude coordinate of the space                     |
| longitude           | REAL       | NOT NULL                         | Longitude coordinate of the space                    |
| other columns...   | ...         | ...                               | Other relevant attributes for each space             |


**Table: `entrances`**

| Column Name        | Data Type | Constraints                     | Description                                          |
|---------------------|------------|---------------------------------|------------------------------------------------------|
| id                  | INTEGER    | PRIMARY KEY, AUTOINCREMENT       | Unique identifier for each entrance                 |
| space_id            | INTEGER    | NOT NULL, FOREIGN KEY (spaces) | ID of the space this entrance belongs to             |
| latitude            | REAL       | NOT NULL                         | Latitude coordinate of the entrance                   |
| longitude           | REAL       | NOT NULL                         | Longitude coordinate of the entrance                  |
| street_number       | TEXT       |                                 | Street number of the entrance                        |
| street              | TEXT       | NOT NULL                         | Street name of the entrance                          |
| city                | TEXT       | NOT NULL                         | City name of the entrance                            |
| other columns...   | ...         | ...                               | Other relevant attributes for each entrance          |


**Table: `schools` (Populated via Frontend)**

| Column Name        | Data Type | Constraints                     | Description                                          |
|---------------------|------------|---------------------------------|------------------------------------------------------|
| id                  | INTEGER    | PRIMARY KEY, AUTOINCREMENT       | Unique identifier for each school                   |
| name                | TEXT       | NOT NULL                         | Name of the school                                    |
| latitude            | REAL       | NOT NULL                         | Latitude coordinate of the school                    |
| longitude           | REAL       | NOT NULL                         | Longitude coordinate of the school                   |
| other columns...   | ...         | ...                               | Other relevant attributes for each school            |


The `spaces` and `entrances` tables should have a spatial index on the latitude and longitude columns to enable efficient spatial queries. The specific SQL commands for creating the tables and indexes will depend on the SQLite version, but generally you would use `CREATE TABLE` and `CREATE INDEX` statements.


### 2. Initial Data Population

The initial database population is handled through the frontend application. The script `crea-changeset-withHeaders.py` is used to import pre-existing data that is structured according to the format expected by the application.


### 3. Map Boundaries and Tile Acquisition

The Python script `crea-changeset-withHeaders.py` retrieves data from a SQLite database (`spaces-perOSM-senza civici preesistenti.db`). You need to determine the geographical boundaries of your map to decide the appropriate tile set. The application uses OpenStreetMap tiles. To obtain these tiles, you will need to use a suitable tile server URL which will likely need to be specified in the frontend configuration.

**Determining Boundaries:**

The `spaces-perOSM-senza civici preesistenti.db`  database contains the lat/lon data, therefore, you can use a query to determine the min/max lat/lon values of the data.  For example using SQL:

```sql
SELECT MIN(latitude), MAX(latitude), MIN(longitude), MAX(longitude) FROM entrances;
```

This will give you the bounding box which you need to define your map's extent.

**Obtaining Tiles:**

Once you know the bounding box, you can use any online map tile provider (e.g., OpenStreetMap) and adjust your frontend JavaScript code to fetch the tiles using the appropriate tile server URL.  The tiles are likely handled by using leafletJS or another mapping JavaScript library which you would need to configure to work with your data and chosen tile server.


### 4. Heatmap Generation in QGIS

To create the heatmap layer in QGIS:

1. **Import Data:** Import the `entrances` table (or a suitable table with population density data) into QGIS.  Ensure that latitude and longitude fields are properly defined as coordinate points.  You may need to use the "Add Delimited Text Layer" tool to do this.
2. **Create Heatmap:** Use the "Heatmap" tool (found under "Raster" -> "Interpolation" -> "Heatmap") within QGIS. This will produce a raster layer that shows population density as a heatmap.  Adjust the radius, decay and other parameters to create a visually appealing and informative heatmap.
3. **Export Heatmap:** Export this heatmap as a suitable format (e.g., GeoTIFF) that you can then use to incorporate it into your web app. This will likely involve converting the exported heatmap into a format usable with your chosen JavaScript mapping library.


### Important Considerations

* **Coordinate System:** Ensure that all your geographical data (database, heatmap) use a consistent coordinate reference system (CRS), preferably EPSG:4326 (WGS 84) which is common for latitude/longitude data.
* **Data Cleaning:** Before importing into the database, you will likely need to clean and validate your spatial data. Check for inconsistencies, duplicates, or incorrect values.
* **Frontend Integration:** The final step involves integrating the heatmap (e.g., as an image tile layer) into your frontend map using a JavaScript mapping library.
