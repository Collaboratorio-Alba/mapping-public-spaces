import osmiter
import geopy.distance
import sys
import json
import sqlite3

osmFile = sys.argv[1]
#db_filepath = sys.argv[2]
maxlenght = 65

filterRoads = ['secondary','tertiary','residential','living_street','service','pedestrian','track','footway','steps','path','sidewalk','crossing','cycleway','unclassified']
roads = []
nodes = {}
apartments = [] # residential buildings, houses and apartments
libraries = [] # load from database - libraries, open community spaces and study rooms
schools = [] # load from file
parks = [] # parks and freely accessible pitches
bus_stops = []
marketplaces = []
immobiliComunali = [] # 230 al 2018 da https://www.dt.mef.gov.it/it/attivita_istituzionali/patrimonio_pubblico/censimento_immobili_pubblici/open_data_immobili/dati_immobili_2018.html
terreniComunali = [] # 165 al 2018


def create_connection(db_file):
    """ create a database connection to the SQLite database
        specified by the db_file
    :param db_file: database file
    :return: Connection object or None
    """
    conn = None
    try:
        conn = sqlite3.connect(db_file)
    except Error as e:
        print(e)

    return conn

def select_place_by_placetype(conn, place_type):
    """
    Query tasks by type
    :param conn: the Connection object
    :param priority:
    :return:
    """
    cur = conn.cursor()
    cur.execute("SELECT * FROM tasks WHERE place_type=?", (place_type,))

    rows = cur.fetchall()

    for row in rows:
        print(row)


# filter real walkable ways
for feature in osmiter.iter_from_osm(osmFile,"xml",filterRoads):
    if feature["type"] == "way" and "tag" in feature.keys():
        if "highway" in feature['tag'].keys():
            roads.append(feature)
    if feature["type"] == "node":
        nodes[feature['id']] = (feature['lat'],feature['lon'])

            
# list nodes that are not part of other roads. Such nodes will be removed after calculating the distance from others.
uniq = {}
for road in roads:
    for node in road['nd']:
        if node in uniq:
            uniq[node] += 1
        else:
            uniq[node] = 1

unic = [n for n, uniq[n] in uniq.items() if uniq[n] == 1]
nds = set()
#remove listed nodes from roads, and sum the dst.
#nodes     [A B C]  -->   [A   C]
#dsts   3 4   -->       7
for road in roads:
    del road['tag']
    del road['type']
    indx = 1
    road['dst'] = []
    copy_nd = road['nd'].copy()
    for node in road['nd'][1:]:
        # calculate dst and remove node 
        d1 = round(geopy.distance.geodesic( nodes[road['nd'][indx-1]], nodes[road['nd'][indx]] ).m , 2)
        road['dst'].append(d1)
        indx += 1
    indx = 1
    for node in road['nd'][1:-1]:
        if node in unic:
            # only if dst < threshold
            newdst = round(road['dst'][indx-1] + road['dst'][indx] , 2)
            if newdst < maxlenght:
                copy_nd.remove(node)
                road['dst'][indx-1] = 0
                road['dst'][indx] = newdst
        indx += 1
    copy_dst = []
    for dist in road['dst']:
        if dist != 0:
            copy_dst.append(dist)
    road['nd'] = copy_nd
    road['dst'] = copy_dst
    for n in road['nd']:
        nds.add(n)

newn = {}
# purge unused nodes
for node in nodes:
    if node in nds:
        newn[node] = nodes[node]

# improve the data structure for nodes: {nodeID: [lat,lon,[[adiacent node ID,distance],...]]}
for nid,nval in newn.items():
    # find which road uses this node
    near_nodes = []
    used_by = []
    for r in roads:
        if nid in r['nd']:
            used_by.append(r)
    for r in used_by:
        #next
        currentNodeindex = r['nd'].index(nid)
        if currentNodeindex + 1 < len(r['nd']):
            #pick the next node id and distance
            nextnode = [r['nd'][currentNodeindex + 1],r["dst"][currentNodeindex]]
            near_nodes.append(nextnode)
        #previous
        if currentNodeindex > 0:
            #pick the previous node id and distance
            prevnode = [r['nd'][currentNodeindex - 1],r["dst"][currentNodeindex - 1]]
            near_nodes.append(prevnode)
    newn[nid] = (nval[0], nval[1], near_nodes)
            

################################
import math

def distance(lat1, lon1, lat2, lon2):
    """
    Calculates the distance between two points on the Earth's surface using the Haversine formula.

    Parameters:
    - lat1: float
        Latitude of the first point in degrees.
    - lon1: float
        Longitude of the first point in degrees.
    - lat2: float
        Latitude of the second point in degrees.
    - lon2: float
        Longitude of the second point in degrees.

    Returns:
    - float:
        The distance between the two points in kilometers.
    """

    # Convert degrees to radians
    lat1_rad = math.radians(lat1)
    lon1_rad = math.radians(lon1)
    lat2_rad = math.radians(lat2)
    lon2_rad = math.radians(lon2)

    # Haversine formula
    dlon = lon2_rad - lon1_rad
    dlat = lat2_rad - lat1_rad
    a = math.sin(dlat/2)**2 + math.cos(lat1_rad) * math.cos(lat2_rad) * math.sin(dlon/2)**2
    c = 2 * math.atan2(math.sqrt(a), math.sqrt(1-a))
    distance = 6371 * c  # Earth's radius is approximately 6371 kilometers

    return distance


def add_equally_spaced_nodes(network, threshold, max_length):
    """
    Adds enough equally spaced nodes to the network to satisfy the distance conditions.

    Parameters:
    - network: dict
        The network of nodes represented as {nodeID: [lat, lon, [[adjacent node ID, distance], ...]]}.
    - threshold: float
        The chosen threshold for the distance.
    - max_length: float
        The maximum length allowed for the distance between nodes.

    Returns:
    - dict:
        The updated network with the added equally spaced nodes.

    Raises:
    - ValueError:
        Raises an error if the threshold or max_length is less than or equal to zero.
    """

    # Validating the threshold and max_length
    if threshold <= 0 or max_length <= 0:
        raise ValueError("Threshold and max_length should be greater than zero.")

    # Create a copy of the network to avoid modifying the original network
    updated_network = network.copy()

    # Iterate over each node in the network
    for node_id, node_data in network.items():
        lat, lon, adjacent_nodes = node_data

        # Iterate over each adjacent node
        for adj_node_id, node_distance in adjacent_nodes:
            # Check if the distance is greater than the threshold
            if node_distance > threshold:
                # Calculate the number of equally spaced nodes needed
                num_nodes = math.ceil(node_distance / max_length) - 1

                # Calculate the latitude and longitude differences
                lat_diff = (network[adj_node_id][0] - lat) / (num_nodes + 1)
                lon_diff = (network[adj_node_id][1] - lon) / (num_nodes + 1)

                # Add the equally spaced nodes
                for i in range(1, num_nodes + 1):
                    new_lat = lat + i * lat_diff
                    new_lon = lon + i * lon_diff
                    new_node_id = f"n_{node_id}_{adj_node_id}_{i}"

                    # Calculate the distance between the new node and the adjacent node
                    #new_distance = distance(lat, lon, new_lat, new_lon)
                    new_distance = round(geopy.distance.geodesic( [lat, lon], [new_lat, new_lon] ).m , 2)

                    # Update the network with the new node and its distance to the adjacent node
                    updated_network[new_node_id] = [new_lat, new_lon, [[adj_node_id, new_distance]]]

    return updated_network

# Example usage:

# Define the network of nodes
network = {
    "node1": [40.7128, -74.0060, [["node2", 10], ["node3", 20]]],
    "node2": [34.0522, -118.2437, [["node1", 10], ["node3", 30]]],
    "node3": [51.5074, -0.1278, [["node1", 20], ["node2", 30]]]
}

# Define the threshold and max_length
threshold = maxlenght
max_length = 50

# Add equally spaced nodes to the network
updated_network = add_equally_spaced_nodes(newn, threshold, max_length)

# Print the updated network
#print(updated_network)
################################


## TODO: bake: add distance from amenities for each node!
# residential buildings counting number of flats

# schools

# libraries, open community spaces and study rooms

# parks and freely accessible pitches

# bus stops

# supermarket/convenience store/marketplace


print(len(roads))
print(len(newn))
#data = {'roads':roads,
#        'nodes':newn}

with open('nodes.json', 'w', encoding='utf-8') as f:
    json.dump(updated_network, f, ensure_ascii=False, indent=2)


