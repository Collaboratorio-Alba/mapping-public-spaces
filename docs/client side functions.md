The provided JavaScript files (`alg.js` and `mps.js`) are part of a web-based mapping application that focuses on geospatial data visualization and interaction. Below is an analysis of the **main functionalities** of each file:

---

## **1. `alg.js` (Algorithmic Functions)**
This file contains core algorithmic and mathematical functions related to geospatial calculations and data processing. Here are the main functionalities:

### **a. Geospatial Calculations**
- **`haversineDistance`**: Calculates the distance between two geographic coordinates (latitude, longitude) using the Haversine formula. This is used to compute distances between points on the Earth's surface.
- **`tiangleArea`**: Computes the area of a triangle using Heron's formula, given the lengths of its sides.
- **`area`**: Calculates the area of a polygon defined by a set of points (latitude, longitude) and a center point. It uses the `haversineDistance` and `tiangleArea` functions to break the polygon into triangles and sum their areas.

### **b. Node and Graph Operations**
- **`nearestNodeID`**: Finds the nearest node (from a graph of nodes) to a given latitude/longitude coordinate. It uses the `adaptiveFilterBox` function to limit the search area and improve performance.
- **`adaptiveFilterBox`**: Dynamically adjusts the search area for nodes based on proximity to a given point. It starts with a small bounding box and expands it until it finds nodes.
- **`filterPointsByCoordinates`**: Filters a dictionary of points (nodes) based on their proximity to a given center point within a specified bounding box.

### **c. Isochrone and Polyline Calculations**
- **`findIsodistancePolyline`**: Computes an isodistance polyline (a line representing equal travel distance) from a given node. It traverses the graph of nodes and connections to determine reachable areas within specified distances (e.g., 5, 10, and 15 minutes of walking).
- **`distToSegmentSquared`**: Calculates the squared distance between a point and a line segment. This is used in polyline and isochrone calculations.

### **d. Convex Hull Calculation**
- **`findConvexHull`**: Computes the convex hull of a set of points using the Graham's scan algorithm. The convex hull is used to simplify the shape of isochrone polygons.

### **e. Utility Functions**
- **`isEmpty`**: Checks if an object is empty.
- **`centerOfPoints`**: Computes the centroid (average latitude and longitude) of a set of points.
- **`areaOfPolygons`**: Computes the areas of multiple polygons using the `area` function.

---

## **2. `mps.js` (Mapping and User Interaction)**
This file handles the frontend logic for the mapping application, including user interaction, data visualization, and communication with the backend API. Here are the main functionalities:

### **a. Map Initialization and Configuration**
- **`initMap`**: Initializes the Leaflet.js map, sets the initial view, and adds base layers (e.g., OpenStreetMap tiles).
- **`mapOptions`**: Configures map settings such as zoom levels, bounds, and canvas rendering preferences.
- **`mapClick`**: Handles map click events, allowing users to add new places or interact with existing markers.

### **b. Data Loading and Visualization**
- **`loadSpaces`**: Fetches and displays geospatial data (e.g., public spaces) from the backend API.
- **`loadEntrances`**: Loads and visualizes building entrances (e.g., houses) as circle markers on the map.
- **`addPlace`**: Adds a new marker to the map for a public space, including its metadata and isochrone polygons.
- **`drawIsochrone`**: Computes and visualizes isochrone polygons (areas reachable within 5, 10, and 15 minutes of walking) for a given marker.

### **c. User Interaction and Editing**
- **`onMarkerClick`**: Handles marker click events, displaying detailed information about the selected space.
- **`markerMoved`**: Updates the position of a marker when it is dragged by the user, recalculating its isochrone polygons.
- **`onCircleEntranceClick`**: Handles click events on building entrance markers, allowing users to edit entrance details.
- **`updatePlace`**: Sends updated marker data (e.g., position, metadata) to the backend API.

### **d. User Authentication and Management**
- **`setMe`**: Sets the current user's session data after successful login.
- **`unsetMe`**: Clears the user's session data on logout.
- **`registerMe`**: Handles user registration and displays appropriate messages.
- **`loginError` and `registerError`**: Handle authentication errors and display error messages.

### **e. Isochrone and Spatial Analysis**
- **`recomputeDistances`**: Recalculates isochrone polygons and spatial statistics for a marker when its position changes.
- **`extractSpatialData`**: Extracts and updates spatial statistics (e.g., population density, reachable areas) based on isochrone calculations.

### **f. UI Components and Controls**
- **`toggleUserPanel`**: Toggles the visibility of the user profile panel.
- **`togglePopulationOverlay`**: Toggles the visibility of a population density overlay on the map.
- **`filterButtonClick`**: Handles filter button clicks (currently a placeholder for filtering logic).
- **`onInfoButtonClick`**: Displays a welcome popup with application information.

### **g. Data Upload and Image Handling**
- **`onFileSelected`**: Handles file selection for uploading images to a public space.
- **`postImage`**: Uploads selected images to the server and updates the marker's metadata.

### **h. Miscellaneous Utilities**
- **`colorizeMarker`**: Applies custom styling to markers based on their metadata (e.g., topic or vocation).
- **`redrawOctosliderCanvas`**: Redraws a radial slider UI component used for categorizing public spaces.
- **`todoOnload` and `todoAfterLoad`**: Initialize event listeners and perform setup tasks after the page loads.

---

## **Summary of Key Functionalities**
- **`alg.js`** focuses on geospatial calculations, including distance, area, isochrone, and convex hull computations.
- **`mps.js`** handles the frontend logic for map interaction, data visualization, user authentication, and communication with the backend API.

Together, these files enable users to:
1. Visualize geospatial data (e.g., public spaces, building entrances) on an interactive map.
2. Perform spatial analysis (e.g., isochrone calculations, area measurements).
3. Interact with the map (e.g., add/edit markers, view details).
4. Authenticate and manage user accounts.
5. Upload and display images associated with public spaces.
