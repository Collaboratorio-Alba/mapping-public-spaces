import osmiter
import geopy.distance
import sys
import json
import sqlite3

osmFile = sys.argv[1]
db_filepath = sys.argv[2]

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
            copy_nd.remove(node)
            newdst = round(road['dst'][indx-1] + road['dst'][indx] , 2)
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
    json.dump(newn, f, ensure_ascii=False, indent=2)


