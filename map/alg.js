
function nearest_node_ID(latlon) {
    let verts = adaptive_filter_bbox(latlon);
    let min = Number.POSITIVE_INFINITY;
    let minID = 0;
    for (let key in verts) {
        let d12 = dist2({x:verts[key][0],y:verts[key][1]}, {x:latlon[0],y:latlon[1]})
        if (d12 < min) {
            min = d12;
            minID = key;
        }
    }
    let hdist = haversineDistance(latlon, ways_data_ntts[minID]);
    return [minID, hdist];
}

// 
function haversineDistance(coords1, coords2) {
    function toRad(x) {
        return x * Math.PI / 180;
    }
    
    var lon1 = coords1[0];
    var lat1 = coords1[1];

    var lon2 = coords2[0];
    var lat2 = coords2[1];
    
    var R = 6371; // km
    
    var x1 = lat2 - lat1;
    var dLat = toRad(x1);
    var x2 = lon2 - lon1;
    var dLon = toRad(x2)
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c * 1000;
    
    return d.toFixed(2); //meters
}

// try to limit the number of nodes based on distance, it returns as soon as it finds some node
function adaptive_filter_bbox(latlon) {
    var n_filt = {};
    let xspan = 0.0005;
    let yspan = 0.00065;
    while (Object.keys(n_filt).length == 0) {
        n_filt = filterPointsByCoordinates(ways_data_ntts,latlon,xspan,yspan);
        xspan += 0.0005;
        yspan += 0.00065;
    }
    return n_filt;
}

/**
 * Filters a dictionary of points based on their latitude and longitude coordinates.
 *
 * @param {Object} points - The dictionary of points to filter.
 * @param {number} centerLat - The latitude of the center of the rectangle.
 * @param {number} centerLng - The longitude of the center of the rectangle.
 * @param {number} extentLat - The extent of the rectangle in latitude units.
 * @param {number} extentLng - The extent of the rectangle in longitude units.
 * @returns {Object} The filtered dictionary of points.
 */
function filterPointsByCoordinates(points, pnt, extentLat, extentLng) {
    // Create an empty object to store the filtered points
    const filteredPoints = {};
    const centerLat = pnt[0];
    const centerLng = pnt[1];
    // Iterate over each key-value pair in the points dictionary
    for (const key in points) {
        if (points.hasOwnProperty(key)) {
            // Get the latitude and longitude values from the array
            const [lat, lng] = points[key];

            // Check if the point is within the specified rectangle
            if (
                lat >= centerLat - extentLat / 2 &&
                lat <= centerLat + extentLat / 2 &&
                lng >= centerLng - extentLng / 2 &&
                lng <= centerLng + extentLng / 2
            ) {
                // Add the point to the filteredPoints object
                filteredPoints[key] = points[key];
            }
        }
    }

    // Return the filteredPoints object
    return filteredPoints;
}

function sqr(x) { return x * x }
function dist2(v, w) { return sqr(v.x - w.x) + sqr(v.y - w.y) }
// Function to return the minimum distance
// between a line segment AB and a point E
function distToSegmentSquared(p, v, w) {
  var l2 = dist2(v, w);
  if (l2 == 0) return dist2(p, v);
  var t = ((p.x - v.x) * (w.x - v.x) + (p.y - v.y) * (w.y - v.y)) / l2;
  t = Math.max(0, Math.min(1, t));
  return dist2(p, { x: v.x + t * (w.x - v.x),
                    y: v.y + t * (w.y - v.y) });
}
function distToSegment(p, v, w) { return Math.sqrt(distToSegmentSquared(p, v, w)); }


/**
 * Function to find the coordinates of an isodistance polyline.
 *
 * @param {Object} dictionary - The dictionary containing node ids and their corresponding latitude, longitude, and connections.
 * @param {string} nodeId - The node id for which the isodistance polyline needs to be found.
 * @param {number} distance - The distance for the isodistance polyline.
 * @returns {Array} The coordinates of the isodistance polyline.
 */
function findIsodistancePolyline(dictionary, nodeId, distance, currentDist) {
    // Check if the node id exists in the dictionary
    if (!dictionary.hasOwnProperty(nodeId)) {
        throw new Error("Node id does not exist in the dictionary.");
    }
    
    const distance15 = distance;
    const distance10 = distance*2/3;
    const distance5 = distance/3;

    // Get the latitude and longitude of the starting node
    const startNode = dictionary[nodeId];
    const startLat = startNode[0];
    const startLng = startNode[1];

    // Initialize an array to store the coordinates of the isodistance polyline <5,<10,<15min
    const polylineCoordinates = [[[startLat, startLng]],[[startLat, startLng]],[[startLat, startLng]]];
    
    // for each visited node, store the distance reached, starting with the max_distance. if another path allows to cross the node with a lower distance, try it and update the distance for that node, otherwise skip the path.
    const visitedNodes = {};
    for (let n in dictionary) {
        visitedNodes[n] = distance15;
    };

    // Recursive function to find the coordinates of the isodistance polyline
    function findCoordinates(currentNodeId, currentDistance) {
        // Check if the current distance is greater than or equal to the desired distance
        if (currentDistance > distance15) {
            return;
        }

        // Get the connections of the current node
        const connections = dictionary[currentNodeId][2];

        // Iterate through the connections
        for (let i = 0; i < connections.length; i++) {
            const connectedNodeId = connections[i][0];
            const connectedDistance = parseInt(connections[i][1]);
            
            let newDistance = currentDistance + connectedDistance;
            // Check if the connected distance is within the desired range
            
            if (newDistance <= distance15) {
                var arrayAddress = 0;
                // choose the array for the appropriate range
                if (newDistance >= distance10) {
                    arrayAddress = 2;
                } else if (newDistance >= distance5) {
                    arrayAddress = 1;
                }
                
                // Add the coordinates of the connected node to the polyline
                // Check if the connected node is already included in the polyline
                if (polylineCoordinates[arrayAddress].some(coord => coord[0] === dictionary[connectedNodeId][0] && coord[1] === dictionary[connectedNodeId][1])) {
                    if (visitedNodes[connectedNodeId] > newDistance) {
                        visitedNodes[connectedNodeId] = newDistance;
                        findCoordinates(connectedNodeId, newDistance);
                    } else {
                    continue;
                    };
                } else {
                    polylineCoordinates[arrayAddress].push([dictionary[connectedNodeId][0], dictionary[connectedNodeId][1]]);
                    // Recursively find the coordinates of the connected node
                    findCoordinates(connectedNodeId, newDistance);
                }
            } //TODO: also calculate the remaining part of a segment crossing the limit and stop propagation.
        }
    }

    // Start finding the coordinates of the isodistance polyline
    findCoordinates(nodeId, parseInt(currentDist));

    return polylineCoordinates;
}


/**
 * Function to find the convex hull around a set of points.
 *
 * @param {Array<Array<number>>} points - Array of points represented as [latitude, longitude].
 * @returns {Array<Array<number>>} Convex hull points represented as [latitude, longitude].
 */
function findConvexHull(points) {
    /**
     * Function to determine the orientation of three points.
     *
     * @param {Array<number>} p - First point.
     * @param {Array<number>} q - Second point.
     * @param {Array<number>} r - Third point.
     * @returns {number} Orientation value.
     */
    function orientation(p, q, r) {
        const val = (q[1] - p[1]) * (r[0] - q[0]) - (q[0] - p[0]) * (r[1] - q[1]);
        if (val === 0) return 0;  // Collinear
        return (val > 0) ? 1 : 2;  // Clockwise or Counterclockwise
    }

    /**
     * Function to find the convex hull using the Graham's scan algorithm.
     *
     * @param {Array<Array<number>>} points - Array of points.
     * @returns {Array<Array<number>>} Convex hull points.
     */
    function grahamScan(points) {
        const n = points.length;
        if (n < 3) return [];  // Convex hull not possible

        // Find the leftmost point
        let leftmostIndex = 0;
        for (let i = 1; i < n; i++) {
            if (points[i][0] < points[leftmostIndex][0]) {
                leftmostIndex = i;
            }
        }

        let hull = [];
        let p = leftmostIndex;
        let q;

        do {
            hull.push(points[p]);
            q = (p + 1) % n;

            for (let i = 0; i < n; i++) {
                if (orientation(points[p], points[i], points[q]) === 2) {
                    q = i;
                }
            }

            p = q;
        } while (p !== leftmostIndex);

        return hull;
    }

    // Sort points by latitude in ascending order
    points.sort((a, b) => a[0] - b[0]);

    // Find the convex hull using Graham's scan algorithm
    const convexHull = grahamScan(points);

    return convexHull;
}

