# Project Documentation

## Purpose
The project is a web-based mapping application designed to visualize geospatial data interactively. Its primary purpose is to display layered map information using pre-generated map tiles and custom data overlays. The application allows users to explore spatial datasets through a user-friendly interface, making it suitable for purposes such as data analysis, educational tools, or real-time monitoring systems.

---

## Architecture Overview
The project follows a client-server architecture (if a backend exists) or a static client-only structure. Below is a breakdown of key components:

### 1. **Frontend**
- **Technologies**: HTML, JavaScript, and CSS.
- **Mapping Library**: Likely Leaflet.js or OpenLayers (assumed based on map tile usage).
- **Components**:
  - `index.html`: Entry point rendering the map container and UI elements.
  - `js/`: Contains application logic (e.g., `app.js` for map initialization, event handling, and data layer integration).
  - `css/`: Stylesheets for UI customization.
  - `tiles/`: Pre-rendered map tiles (e.g., in `/{z}/{x}/{y}.png` structure) served as a base layer.

### 2. **Backend (if applicable)**
- If present, a lightweight server (e.g., Node.js/Express or Python/Flask) serves static assets and handles API requests for dynamic data.
- Endpoints may include:
  - `/data/`: Serves geospatial files (GeoJSON, TopoJSON) from the `data/` folder.
  - `/config/`: Returns configuration variables (e.g., initial map settings).

### 3. **Data Management**
- **Data Folder**: Stores geospatial datasets (e.g., GeoJSON, CSV) used for overlays.
- **Tile Management**: The `tiles/` folder contains static map tiles, typically organized in `z/x/y` directory structure for slippy maps.

---

## Configuration Variables
Key configuration options (set via `config.js`, environment variables, or inline in HTML):

| Variable          | Purpose                                | Example Value                  |
|-------------------|----------------------------------------|---------------------------------|
| `MAP_CENTER`      | Initial map view center (lat, lng).    | `[51.505, -0.09]`              |
| `ZOOM_LEVEL`      | Default zoom level on load.            | `13`                           |
| `TILES_URL`       | Path/URL to map tiles.                 | `'/tiles/{z}/{x}/{y}.png'`     |
| `MAX_ZOOM`        | Maximum allowed zoom level.            | `18`                           |

---

## Data Folder Structure
The `data/` folder contains geospatial datasets used to render overlays on the map. Supported formats include:
- **GeoJSON/TopoJSON**: For vector layers (e.g., points, polygons).
- **CSV**: Tabular data with geographic coordinates (lat/lng columns).
- **Raster Files**: (Less likely, as tiles are pre-generated.)

### Usage:
- Data is loaded dynamically by the frontend (e.g., via `fetch` in JavaScript).
- Files are parsed and converted into map layers (e.g., Leaflet’s `L.geoJSON()`).

---

## Tile Folder Note
The `tiles/` directory contains pre-rendered raster or vector tiles following the **slippy map** naming convention (`{z}/{x}/{y}.png`). These tiles are either:
- Generated using tools like `gdal2tiles` or `tippecanoe`.
- Served statically or via a tile server (e.g., Apache, NGINX).

No further inspection of this folder is required, as tile generation and storage are considered static assets.

---

## Setup & Customization
0. **Vendor libraries**: Install required libraries.
1. **Configure Variables**: Modify `index.php` and  `map/mps.js` to adjust map defaults.
2. **Add Data**: Place new datasets in the `data/` folder and update the frontend to load them.
3. **Tiles**: Replace tiles in `tiles/` if the base map needs updating (requires re-generating tiles).
4. **Database**: prepare it with the scripts found in `api/` folder and place the database in a folder not visible from the web

---

This document provides a high-level overview. For implementation details, refer to source code comments or supplementary technical guides.
