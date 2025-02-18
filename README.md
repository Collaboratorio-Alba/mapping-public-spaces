# mapping-public-spaces: Interactive Geospatial Mapping Application

This project is a web-based interactive mapping application designed for visualizing and analyzing geospatial data related to vacant properties and public spaces. It uses a client-server architecture with a lightweight PHP backend and a dynamic frontend built using Leaflet.js.  The project aims to document public spaces and vacancy situations in cities to stimulate discussion about potential uses and urban development.

## Motivation

It is best to be clear about the criteria, measurements, and ontologies we intend to employ before we begin defining the spaces.
A viable method for characterizing social public spaces is to combine user feedback, qualitative observations, and objective criteria.

Some fields, specifically those involving the propension of visitors to spend some time in a particular space, require user feedback, but it is up to those who implement the platform to choose between a questionnaire, interviews or indirect sources.
Other fields, based on observations, can be filled by the person carrying out the survey/monitoring.

We tried to define an ontology by identifying common traits between spaces, spaces that are also very different in other respects. 
This criterion considers 8 different themes that involve aggregation: physical activity, community, creativity, education, care, contemplation, activism, nature.

For each theme, the propensity of citizens to take advantage of the possibilities offered by the individual space is estimated;
the quantification measures the distance, in walking minutes, that citizens travel to reach the space.
Such distance is aggregated as a value from 0 to 3 which summarizes the size of the area of influence of such space regarding the said theme on the urban territory.
To support this analysis the platform calculates and represents concentric areas defined by isochronous curves based on 5 minute walking intervals.

## Project Overview

The application allows users to:

* **Visualize geospatial data:** Display pre-generated map tiles as a base layer and overlay crowdsourced data about public spaces, building entrances, and schools.
* **Perform spatial analysis:**  Calculate isochrones (areas reachable within a given walking time) to understand the accessibility and area of influence of public spaces.
* **Interact with the map:** Add, edit, and delete markers representing places of interest. Upload images associated with locations.
* **User Authentication:**  The backend supports user registration, login, logout, and password management through a database authentication system via a slightly customized version of PHP-CRUD-API.

## Key Features

* **Crowdsourced Data:** Data about public spaces and vacant properties is collected via the web interface (features, description, technical informations, history, actors involved, ...) and a LimeSurvey form or similar methods (user point of view and subjective experience of the place).
* **Social Public Space Characterization:** Combines user feedback, qualitative observations, and objective criteria to define and categorize spaces.
* **Ontology-based Categorization:** Spaces are categorized based on eight themes: physical activity, community, creativity, education, care, contemplation, activism, and nature.
* **Isochrone Analysis:**  Isochrone polygons are calculated to estimate the area of influence of each space for each theme, measured in walking minutes and aggregated into values from 0 to 3.
* **OpenStreetMap Integration:** The application uses OpenStreetMap tiles for the base map and supports exporting data in OSM XML format for contributing back to OpenStreetMap.

## Architecture

The application follows a client-server architecture:

**1. Frontend (Client-side):**

* **Technologies:** HTML, CSS, JavaScript (Leaflet.js, custom scripts: `alg.js`, `mps.js`).
* **`index.php`:** The main HTML file that renders the map and UI elements.
* **`js/` directory:** Contains the core frontend logic.  See [Client-Side Functions](docs/client%20side%20functions.md) documentation for details.
* **`css/` directory:** Contains CSS stylesheets for styling the application's UI.
* **`tiles/` directory:** Stores pre-generated map tiles.


**2. Backend (Server-side):**

* **Technologies:** PHP (using PHP-CRUD-API framework), Python scripts for data processing.
* **`api/` directory:** Contains the PHP backend API and data processing scripts.
* **Database:** Uses a SQLite database (`spaces.db`) to store geospatial data. See [Database Initialization](docs/Database%20initialization.md) documentation for details.
* **Vendor Libraries:** See [Vendor Libraries](docs/vendor.md) for details on installing backend dependencies.


## Getting Started

1. **Install Dependencies:** Refer to the [Vendor Libraries](docs/vendor.md) documentation to install the backend PHP and Python dependencies. For frontend dependencies (Leaflet.js), consult the frontend documentation or relevant online resources.
2. **Database Setup:** Create the SQLite database (`spaces.db`) according to the schema described in [Database Initialization](docs/Database%20initialization.md). You can use provided Python scripts in the `api/` directory to help create the necessary tables.
3. **Tile Acquisition:** Download or generate map tiles for your area of interest and place them in the `tiles/` directory. Customize the `TILES_URL` in your frontend configuration ([Project Documentation](docs/Project%20Documentation.md)) to point to the correct tile location.
4. **Heatmap Generation:**  Generate a heatmap representation of population density using QGIS or similar software, and integrate it into your web application.  (Instructions in [Database Initialization](docs/Database%20initialization.md).)
5. **Frontend Configuration:** Modify the JavaScript files in the `js/` directory, specifically `mps.js` and index.php, to set up initial map settings, API endpoints, and other configurations. ([Project Documentation](docs/Project%20Documentation.md))
6. **Run the Application:** Start your web server (e.g. Apache, Nginx) and access `index.php` through your browser.

## Usage:

Follow the italian [Guida all'uso](docs/guida%20uso.md).

[vokoscreenNG-2023-12-01_11-42-34.webm](https://github.com/Collaboratorio-Alba/mapping-public-spaces/assets/6873524/d33f5496-6b38-429c-8b80-8528ca0e868e)

## Contributing

Contributions are welcome! Please open issues or submit pull requests.

## License

[MIT License](LICENSE)
