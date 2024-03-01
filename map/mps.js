'use strict'

/* global jscrudapi, L, nearestNodeID, findIsodistancePolyline, findConvexHull, areaOfPolygons */

const apiUrl = 'https://collab.42web.io/api/api.php'
const houseNumbersUrl = 'https://collab.42web.io/tiles/t'
const mapCenter = [44.6798, 8.0362]
const waysDataFile = '/data/nodes.json'
const waysDataNtts = {}
const peoplePerNtts = {}
const flatDensity = 2.25 // in base a dati ISTAT 2011
let me
let mpsMap
let activeMarker
let activeEntrance
let editorP
let houseNumbersLayer
let entranceMarker
const pedonalDistance15 = 1000 // @ 4km/h , t=15min. Distance of a 15 minute walk
const pedonalDistance10 = parseInt(pedonalDistance15 * 2 / 3)
const pedonalDistance5 = parseInt(pedonalDistance15 / 3)
// const jcaconfigHeaders = {headers : {
// 'Access-Control-Allow-Origin':'*',
// 'Access-Control-Allow-Methods':'GET, POST, PUT, DELETE, PATCH, OPTIONS',
// 'Access-Control-Allow-Headers':'Origin, X-Requested-With, Content, Accept, Content-Type, Authorization'}};
// limits of colored map computation
// const boundingRect = L.latLngBounds(L.latLng(44.6437000,7.9709000), L.latLng(44.7370000,8.0593000));

/**
 * Represents a JavaScript CRUD API client for performing CRUD operations on a specific API endpoint.
 *
 * @param {string} apiUrl - The URL of the API endpoint to interact with.
 * @returns {Object} An instance of the jscrudapi client.
 */
const jca = jscrudapi(apiUrl)

// markerCluster plugin
const markers = L.markerClusterGroup({ disableClusteringAtZoom: 16, removeOutsideVisibleBounds: false })
const houses = L.layerGroup()
const nears = L.layerGroup()
let panelsHidden = true
const mapOptions = {
  preferCanvas: true,
  maxZoom: 19,
  minZoom: 9
}

window.onload = (event) => {
  fetch(waysDataFile)
    .then(response => response.json())
    .then(function (data) {
      for (const [key, value] of Object.entries(data)) {
        waysDataNtts[key] = value
      }
      if (typeof me === 'undefined') {
        jca.me().then(
          data => setMe(data)
        ).catch(
          error => loginError(error)
        )
      }
      todoOnload()
    }).catch(
      error => console.log(error)
    )
}

function initMap () {
  mpsMap = L.map('mpsMap', mapOptions).setView(mapCenter, 14)
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>, <a href="https://collab.42web.io/terms-and-conditions.php">Collaboratorio Alba</a>, <a href="https://github.com/Collaboratorio-Alba/mapping-public-spaces"><img class="githubLogo" src="images/github-mark.svg" alt="Github repository"/></a>'
  }).addTo(mpsMap)

  // house_numbers layer
  houseNumbersLayer = L.tileLayer(houseNumbersUrl + '/{z}/{y}/{x}', {
    minZoom: 19,
    maxZoom: 19
  })

  mpsMap.on('click', mapClick)
  mpsMap.addLayer(markers)
  mpsMap.addLayer(houses)
  mpsMap.addLayer(nears)
  loadSpaces()
  mpsMap.on('click', allToBackground)
  L.control.scale().addTo(mpsMap)
  // Create a container div for the buttons
  const container = L.DomUtil.create('div', 'leaflet-control leaflet-bar')
  // create user button
  const userButtonElement = L.DomUtil.create('a', 'leaflet-user-button', container)
  userButtonElement.innerHTML = '\u{1F464}'
  userButtonElement.setAttribute('href', '#')
  userButtonElement.id = 'usericon'
  L.DomEvent.disableClickPropagation(userButtonElement)
  L.DomEvent.on(userButtonElement, 'click', toggleUserPanel)
  // Create the filter button
  const filterButtonElement = L.DomUtil.create('a', 'leaflet-control-filter-button', container)
  filterButtonElement.setAttribute('href', '#')
  filterButtonElement.title = 'Filtro'
  filterButtonElement.innerHTML = '\u{29E9}'
  filterButtonElement.id = 'filter'
  L.DomEvent.disableClickPropagation(filterButtonElement)
  L.DomEvent.on(filterButtonElement, 'click', filterButtonClick)
  // Create the entrances button
  const entrancesButtonElement = L.DomUtil.create('a', 'leaflet-control-entrances-button', container)
  entrancesButtonElement.setAttribute('href', '#')
  entrancesButtonElement.title = 'Ingressi'
  entrancesButtonElement.innerHTML = '\u{2302}'
  entrancesButtonElement.id = 'entrances'
  L.DomEvent.disableClickPropagation(entrancesButtonElement)
  L.DomEvent.on(entrancesButtonElement, 'click', onEntrancesButtonClick)
  // Add this leaflet control
  const UserControl = L.Control.extend({
    options: {
      position: 'topright'
    },

    onAdd: function () {
      const container = L.DomUtil.create('div')
      container.appendChild(userButtonElement)
      return container
    }
  })
  // Add this leaflet control
  const FilterControl = L.Control.extend({
    options: {
      position: 'topright'
    },

    onAdd: function () {
      const container = L.DomUtil.create('div')
      container.appendChild(filterButtonElement)
      return container
    }
  })
  // Add this leaflet control
  const EntrancesControl = L.Control.extend({
    options: {
      position: 'topright'
    },

    onAdd: function () {
      const container = L.DomUtil.create('div')
      container.appendChild(entrancesButtonElement)
      return container
    }
  })
  mpsMap.addControl(new UserControl())
  mpsMap.addControl(new FilterControl())
  mpsMap.addControl(new EntrancesControl())
  // if(L.Browser.mobile) {
  // } else {
  // };
  markers.eachLayer(function (marker) {
    colorizeMarker(marker)
  })
};

function mapClick (e) {
  // also detect click on existing marker
  if (panelsHidden) {
    if ((typeof me !== 'undefined') && (me !== null)) {
      const name = prompt("Grazie per il tuo contributo! Vuoi aggiungere un luogo?\nSe hai dubbi controlla la guida all'uso toccando l'icona in alto a destra.\n\nCome si chiama questo spazio?")
      if ((name !== null) && (name !== '')) {
        addPlace([e.latlng.lat, e.latlng.lng], name)
      }
    }
  } else {
    allToBackground()
    if (activeMarker !== null) {
      nears.clearLayers()
      activeMarker = null
    }
  }
}

/**
 * Loads spaces from the API and adds them to the UI.
 *
 * This function retrieves a list of spaces from the API, filters out the deleted spaces,
 * and adds the remaining spaces to the UI by calling the addPlace function.
 */
function loadSpaces () {
  jca.list('spaces', { filter: 'deleted,eq,0' })
    .then(function (response) {
      response.records.forEach(function (item, index) {
        addPlace([parseFloat(item.latitude), parseFloat(item.longitude)], item)
      })
    }).catch(
      error => console.log(error)
    )
}

/**
 * Loads talks from the API and adds them to the UI.
 *
 * This function retrieves a list of messages from the API, related to one space,
 * and adds them to the UI by calling the addTalks function.
 */
function loadTalks (spaceID) {
  // clear talk div
  document.getElementById('talk').innerHTML = "";
  jca.list('talks', { filter: 'space_id,eq,'+spaceID, order: 'datetime_created,desc' })
    .then(function (response) {
      response.records.forEach(function (item, index) {
        if (index < 20) {
          document.getElementById('talk').appendChild(formatTalk(item))
        }
      })
    }).catch(
      error => console.log(error)
    )
}

/**
 * Adds a single message to the UI.
 */
function formatTalk (record) {
  const newDiv = document.createElement("div");
  let divClass = "msg_received"
  if (me !== null) {
    if (record.user_id === me.id) {
      divClass = "msg_sent"
    }
  }
  newDiv.classList.add(divClass)
  const metaP = document.createElement("p")
  const metaTextNode = document.createTextNode(record.username + ' - ' + record.datetime_created.substr(0, 16).replace('T',' '))
  metaP.appendChild(metaTextNode)
  const newP = document.createElement("p")
  const textNode = document.createTextNode(record.text)
  newP.appendChild(textNode)
  newDiv.appendChild(metaP)
  newDiv.appendChild(newP)
  return newDiv
}

function loadEntrances () {
  jca.list('entrances')
    .then(function (response) {
      response.records.forEach(function (item, index) {
        const lat = parseFloat(item.latitude)
        const lng = parseFloat(item.longitude)
        const nndist = parseFloat(item.nearest_node_dst)
        const color = item.flats_count >= 0 ? '#72a3eb' : '#ae7a7a'
        const circMar = L.circleMarker([lat, lng], { radius: 5, color })
        circMar.data = item
        circMar.data.latitude = lat
        circMar.data.longitude = lng
        circMar.data.nearest_node_dst = nndist
        circMar.bindTooltip(circMar.data.street_number)
        circMar.on('click', onCircleEntranceClick)
        circMar.addTo(houses)
        // fill a dict with people distribution
        if (circMar.data.inhabited_flats_count !== -1) {
          const peoplePerNtt = circMar.data.inhabited_flats_count * flatDensity
          if (typeof peoplePerNtts[circMar.data.nearest_node_ID] === 'undefined') { peoplePerNtts[circMar.data.nearest_node_ID] = [] }
          peoplePerNtts[circMar.data.nearest_node_ID].push([parseFloat(circMar.data.nearest_node_dst), peoplePerNtt])
        }
      })
    }).catch(
      error => console.log(error)
    )
}

function computeEntrances () {
  jca.list('entrances')
    .then(function (response) {
      response.records.forEach(function (item, index) {
        const nndist = parseFloat(item.nearest_node_dst)
        // fill a dict with people distribution
        if (item.inhabited_flats_count !== -1) {
          const peoplePerNtt = item.inhabited_flats_count * flatDensity
          if (typeof peoplePerNtts[item.nearest_node_ID] === 'undefined') { peoplePerNtts[item.nearest_node_ID] = [] }
          peoplePerNtts[item.nearest_node_ID].push([parseFloat(nndist), peoplePerNtt])
        }
      })
    }).catch(
      error => console.log(error)
    )
}



function colorizeMarker (marker) {
  const topic = marker.data.vocation
  const topics = ['nessuna', 'movimento', 'natura', 'creatività', 'socializzazione', 'cura', 'introspezione', 'attivismo', 'formazione']
  const mappingHue = [0, 25, 270, 240, 190, 145, 90, 320, 0]
  const mappingSat = [1, 1.6, 1.4, 1.8, 1.4, 1.1, 1, 1.1, 1.1]
  if (topics.indexOf(topic) === 0) {
    marker._icon.style.filter = 'saturate(0)'
  } else {
    marker._icon.style.filter = 'brightness(' + mappingSat[topics.indexOf(topic)] + ') hue-rotate(' + mappingHue[topics.indexOf(topic)] + 'deg)'
  }
}

async function addPlace (latlng, data) {
  const mar = L.marker(latlng, { draggable: 'true' }).on('click', onMarkerClick)
  // if creating new space
  if (typeof (data) === 'string') {
    data = await createNewSpace(latlng, data)
    // reload from db
    jca.read('spaces', data
    ).then(function (resp) {
      mar.data = resp
      drawIsochrone(mar)
    })
    // else loading space
  } else {
    mar.data = data
    // add node to nearest neighbour list
    addMarkerToNodes(mar)
    // unserialize
    mar.data.nearest_node_dst = parseFloat(mar.data.nearest_node_dst)
    mar.data.latitude = parseFloat(mar.data.latitude)
    mar.data.longitude = parseFloat(mar.data.longitude)
    mar.data.isochrone5_latlngs = JSON.parse(data.isochrone5_latlngs)
    mar.data.isochrone10_latlngs = JSON.parse(data.isochrone10_latlngs)
    mar.data.isochrone15_latlngs = JSON.parse(data.isochrone15_latlngs)
    mar.data.isochrone_area5 = parseInt(data.isochrone_area5)
    mar.data.isochrone_area10 = parseInt(data.isochrone_area10)
    mar.data.isochrone_area15 = parseInt(data.isochrone_area15)
    drawIsochrone(mar)
  }
  // add nearest node features to mar.data
  mar.on('dragend', e => markerMoved(e))
  mar.on('add', e => colorizeMarker(e.target))
  markers.addLayer(mar)
}

// draw but do not show
function drawIsochrone (marker, forceUpdate = false) {
  const latlng = [parseFloat(marker.data.latitude), parseFloat(marker.data.longitude)]
  const nn = nearestNodeID(latlng, waysDataNtts)
  // detect missing data or moved node this solution is prone to missing updates in case of markers far away from nodes
  if (forceUpdate || ((nn[0] !== marker.data.nearest_node_ID) || (Number.isNaN(marker.data.isochrone_area5)))) {
    recomputeDistances(marker, nn)
    // update record
    if (me !== null) {
      updatePlace(marker, {
        latitude: latlng[0],
        longitude: latlng[1],
        nearest_node_ID: marker.data.nearest_node_ID,
        nearest_node_dst: marker.data.nearest_node_dst,
        isochrone15_latlngs: JSON.stringify(marker.data.isochrone15_latlngs),
        isochrone10_latlngs: JSON.stringify(marker.data.isochrone10_latlngs),
        isochrone5_latlngs: JSON.stringify(marker.data.isochrone5_latlngs),
        isochrone_area5: marker.data.isochrone_area5,
        isochrone_area10: marker.data.isochrone_area10,
        isochrone_area15: marker.data.isochrone_area15
      })
    };
  };
  marker.viewdata = {}
  const coordsNear = waysDataNtts[marker.data.nearest_node_ID].slice(0, 2)
  if (typeof marker.data.isochrone5_latlngs === 'string') {
    marker.data.isochrone5_latlngs = JSON.parse(marker.data.isochrone5_latlngs)
    marker.data.isochrone10_latlngs = JSON.parse(marker.data.isochrone10_latlngs)
    marker.data.isochrone15_latlngs = JSON.parse(marker.data.isochrone15_latlngs)
  };
  marker.viewdata.isochrone5 = L.polygon(marker.data.isochrone5_latlngs, { weight: 1 }).bindTooltip('5 minuti a piedi')
  marker.viewdata.isochrone10 = L.polygon(marker.data.isochrone10_latlngs, { weight: 1 }).bindTooltip('10 minuti a piedi')
  marker.viewdata.isochrone15 = L.polygon(marker.data.isochrone15_latlngs, { weight: 1 }).bindTooltip('15 minuti a piedi')
  marker.viewdata.nearLine = L.polyline([coordsNear, latlng])
  marker.viewdata.near = L.circleMarker(coordsNear, { radius: 5 })
}

// eslint-disable-next-line no-unused-vars
function recomputeAllMarkers () {
  if ((typeof me !== 'undefined') && (me !== null)) {
    markers.eachLayer(function (l) { console.log(l.data.name); drawIsochrone(l, true) })
  }
}

function recomputeDistances (marker, nearNode) {
  // if node has this marker in neighbours list, remove it.
  if ((marker.data.nearest_node_ID != null) && (marker.data.nearest_node_ID in waysDataNtts) && (waysDataNtts[marker.data.nearest_node_ID].lenght === 4)) {
    const markerIndex = waysDataNtts[marker.data.nearest_node_ID][3].indexOf(marker)
    if (markerIndex > -1) {
      waysDataNtts[marker.data.nearest_node_ID][3].splice(markerIndex, 1)
    }
  }
  marker.data.nearest_node_ID = nearNode[0]
  marker.data.nearest_node_dst = nearNode[1]
  const isodista = findIsodistancePolyline(waysDataNtts, peoplePerNtts, marker.data.nearest_node_ID, marker.data.id, [pedonalDistance15, pedonalDistance10, pedonalDistance5], marker.data.nearest_node_dst)
  marker.data.isochrone15_latlngs = findConvexHull(isodista[2])
  marker.data.isochrone10_latlngs = findConvexHull(isodista[1])
  marker.data.isochrone5_latlngs = findConvexHull(isodista[0])
  const areas51015 = areaOfPolygons([marker.data.isochrone5_latlngs, marker.data.isochrone10_latlngs, marker.data.isochrone15_latlngs])
  marker.data.isochrone_area5 = parseInt(areas51015[0])
  marker.data.isochrone_area10 = parseInt(areas51015[1])
  marker.data.isochrone_area15 = parseInt(areas51015[2])
  // isodista[3] is a dict of places found while traversing, and their distance. isodista[4] has the people count inside each isochrone. Use them to save relevant information.
  extractSpatialData(isodista[3], isodista[4], marker)
  addMarkerToNodes(marker)
}

function addValueToDict (dict, key, value) {
  if (key in dict) { dict[key] += value } else { dict[key] = value }
  return dict
}

// copy distances and inhabitants to the pertinent fields
function extractSpatialData (srcMarkerList, peopleCount, dstMarker) {
  const updatedFields = {}
  srcMarkerList.forEach((element) => {
    const ma = element[0]
    const distance = element[1]
    if (['scuola_k', 'scuola_p', 'scuola_m', 'scuola_h'].includes(ma.data.type)) {
      let distanceSuffix
      if ((distance > pedonalDistance10) && (distance < pedonalDistance15)) {
        distanceSuffix = '_15_m_walk'
      } else if ((distance > pedonalDistance5) && (distance < pedonalDistance10)) {
        distanceSuffix = '_10_m_walk'
      } else if ((distance >= 0) && (distance < pedonalDistance5)) { distanceSuffix = '_5_m_walk' }
      const fieldName = 'students_' + ma.data.type.slice(-1) + distanceSuffix
      // attendees_yearly
      addValueToDict(updatedFields, fieldName, ma.data.attendees_yearly)
      // if distance is 0-5 add also to 5-10 and 10-15
      if (distanceSuffix === '_5_m_walk') {
        const tenFieldName = fieldName.replace('_5_', '_10_')
        const ftFieldName = fieldName.replace('_5_', '_15_')
        addValueToDict(updatedFields, tenFieldName, ma.data.attendees_yearly)
        addValueToDict(updatedFields, ftFieldName, ma.data.attendees_yearly)
      }
      // if distance is 5-10 add also to 10-15
      if (distanceSuffix === '_10_m_walk') {
        const fifteenFieldName = fieldName.replace('_10_', '_15_')
        addValueToDict(updatedFields, fifteenFieldName, ma.data.attendees_yearly)
      }
    }
  })
  updatedFields.residents_5_m_walk = parseInt(peopleCount[0])
  updatedFields.residents_10_m_walk = parseInt(peopleCount[1])
  updatedFields.residents_15_m_walk = parseInt(peopleCount[2])
  updatedFields.habha_5_m_walk = (peopleCount[0] / (dstMarker.data.isochrone_area5 / 10000)).toFixed(2)
  updatedFields.habha_10_m_walk = (peopleCount[1] / (dstMarker.data.isochrone_area10 / 10000)).toFixed(2)
  updatedFields.habha_15_m_walk = (peopleCount[2] / (dstMarker.data.isochrone_area15 / 10000)).toFixed(2)
  updatePlace(dstMarker, updatedFields)
}

function addMarkerToNodes (marker) {
  // add marker to new nearest node's neighbours list
  try {
    if (waysDataNtts[marker.data.nearest_node_ID].length === 3) {
      waysDataNtts[marker.data.nearest_node_ID].push([])
    }
    if (!waysDataNtts[marker.data.nearest_node_ID][3].includes(marker)) {
      waysDataNtts[marker.data.nearest_node_ID][3].push(marker)
    }
  } catch (err) {
    console.log(err.message)
    console.log('riposizionare marker nearest node')
    console.log(marker.data.name)
  }
}

function markerMoved (event) {
  const marker = event.target
  if (me === null) {
    marker.setLatLng([marker.data.latitude, marker.data.longitude], { draggable: 'false' }).update()
    return
  };
  const position = marker.getLatLng()
  marker.setLatLng(position, { draggable: 'true' }).update()
  marker.data.latitude = parseFloat(position.lat)
  marker.data.longitude = parseFloat(position.lng)
  nears.clearLayers()
  drawIsochrone(marker)
  drawViewdata(marker)
}

function drawViewdata (m) {
  nears.addLayer(m.viewdata.near)
  nears.addLayer(m.viewdata.nearLine)
  nears.addLayer(m.viewdata.isochrone15)
  nears.addLayer(m.viewdata.isochrone10)
  nears.addLayer(m.viewdata.isochrone5)
}

function createNewSpace (latlng, named) {
  let datetimeNow = new Date()
  datetimeNow = datetimeNow.toISOString()
  return jca.create('spaces', [
    {
      name: named,
      latitude: latlng[0],
      longitude: latlng[1],
      version: 0,
      datetime_created: datetimeNow,
      datetime_last_edited: datetimeNow,
      created_by: me.username,
      edited_by: me.username
    }
  ]).catch(
    error => console.log(error)
  )
}

function createNewEntrance (latlng, streetName, streetNumber, city, flatCount, inhabitedFlatCount, nearestNode) {
  let datetimeNow = new Date()
  datetimeNow = datetimeNow.toISOString()
  const enData = {
    street: streetName,
    street_number: streetNumber,
    city,
    latitude: latlng.lat,
    longitude: latlng.lng,
    nearest_node_ID: nearestNode[0],
    nearest_node_dst: nearestNode[1],
    flats_count: flatCount,
    inhabited_flats_count: inhabitedFlatCount,
    datetime_created: datetimeNow,
    created_by: me.username
  }
  jca.create('entrances', [
    enData
  ]).then(function (response) {
    if (Number.isInteger(response[0])) {
      const idEn = response[0]
      console.log(response)
      const color = enData.flats_count >= 0 ? '#72a3eb' : '#ae7a7a'
      const circMar = L.circleMarker(latlng, { radius: 5, color }).bindTooltip(streetNumber).on('click', onCircleEntranceClick)
      circMar.data = enData
      circMar.data.id = idEn
      circMar.addTo(houses)
    }
  }).catch(
    error => console.log(error)
  )
}

function updateEntrance (id, streetName, streetNumber, city, flatCount, inhabitedFlatCount) {
  let datetimeNow = new Date()
  datetimeNow = datetimeNow.toISOString()
  const dataDict = {
    street: streetName,
    street_number: streetNumber,
    city,
    flats_count: flatCount,
    inhabited_flats_count: inhabitedFlatCount,
    datetime_last_edited: datetimeNow,
    edited_by: me.username
  }
  for (const [key, value] of Object.entries(dataDict)) {
    activeEntrance.data[key] = value
  };
  // update marker color
  const color = dataDict.flats_count >= 0 ? '#72a3eb' : '#ae7a7a'
  activeEntrance.setStyle({ color })
  jca.update('entrances', id,
    dataDict).catch(
    error => console.log(error)
  )
}

// marker interaction
function onMarkerClick (e) {
  if (activeMarker !== null) {
    nears.clearLayers()
  }
  activeMarker = this
  placeToForeground(this.data)
  // here calculate, save and visualize spatial statistics.
  drawViewdata(activeMarker)
}

// entrance editing
function onCircleEntranceClick (e) {
  activeEntrance = this
  entrancesToForeground(this.data)
  L.DomEvent.stopPropagation(e)
}

function toggleUserPanel () {
  if (panelsHidden) {
    if (me == null) {
      allToBackground()
      authToForeground()
    } else {
      allToBackground()
      userToForeground()
    };
  } else {
    allToBackground()
  };
}

function toggleTabPanels () {
  if (document.getElementsByClassName('tab-panels')[0].style.height === '0vh') {
    document.getElementsByClassName('tab-panels')[0].style.height = '60vh'
  } else {
    document.getElementsByClassName('tab-panels')[0].style.height = '0vh'
  };
}

function loginError (e) {
  // console.log(e)
  authToForeground()
  unsetMe()
  if (e.code === 1012) {
    document.getElementById('loginMessage').innerText = e.message
  }
}

function registerError (e) {
  // console.log(e)
  authToForeground()
  document.getElementById('registerMessage').innerText = e.message
}

function registerMe (dati) {
  // eslint-disable-next-line no-prototype-builtins
  if (dati.hasOwnProperty('code')) {
    registerError(dati)
  } else {
    allToBackground()
    const registerFields = document.getElementsByClassName('registr')
    for (const f of registerFields) {
      f.value = ''
    }
    document.getElementById('signupCheck').checked = false
    alert('Bin venü! Ti abbiamo inviato una email con il link per confermare il tuo account!!')
  }
}

function setMe (dati) {
  me = dati
  document.getElementById('profileUsername').innerText = me.username
  document.getElementById('profileFirst_name').innerText = me.first_name
  document.getElementById('profileLast_name').innerText = me.last_name
  document.getElementById('profileEmail').innerText = me.email
  const fields = document.getElementsByClassName('ui')
  for (let i = 0; i < fields.length; i++) {
    fields[i].disabled = false
  }
  document.getElementsByClassName('pell-content')[0].setAttribute('contenteditable', 'true')
  document.getElementsByClassName('pell-actionbar')[0].style.display = 'block'
  document.Delete.style.display = 'block' 
  markers.eachLayer(function (l) {
    if (Object.prototype.hasOwnProperty.call(l, 'dragging')) {
      l.dragging.enable()
    }
  })
  allToBackground()
  // this is rude, why not adding an admin field?
  if (me.id === 1) {
    document.getElementById('entrances').parentElement.style.display = 'block'
    if (houses.getLayers().length === 0) {
      loadEntrances()
    }
  } else {
    computeEntrances()
  }
  if (me.id === 1) {
    mpsMap.addLayer(houseNumbersLayer)
  }
}

function unsetMe () {
  if (me != null) {
    if (me.id === 1) {
      mpsMap.removeLayer(houseNumbersLayer)
    }
  }
  me = null
  document.getElementById('profileUsername').innerText = ''
  document.getElementById('profileFirst_name').innerText = ''
  document.getElementById('profileLast_name').innerText = ''
  document.getElementById('profileEmail').innerText = ''
  const fields = document.getElementsByClassName('ui')
  for (let i = 0; i < fields.length; i++) {
    fields[i].disabled = true
  }
  document.getElementsByClassName('pell-content')[0].setAttribute('contenteditable', 'false')
  document.getElementsByClassName('pell-actionbar')[0].style.display = 'none'
  document.Delete.style.display = 'none' 
  markers.eachLayer(function (l) {
    if (Object.prototype.hasOwnProperty.call(l, 'dragging')) {
      l.dragging.disable()
    }
  })
  allToBackground()
  document.getElementById('entrances').parentElement.style.display = 'none'
}

function fillEntranceFields (data) {
  for (const [key, value] of Object.entries(data)) {
    if (document.getElementById('entrances-' + key)) {
      document.getElementById('entrances-' + key).value = value
    }
  }
}

function fillPlaceFields (data) {
  for (const [key, value] of Object.entries(data)) {
    if (document.getElementById('spazi-' + key)) {
      const readonly = document.getElementById('spazi-' + key).readOnly
      document.getElementById('spazi-' + key).readOnly = false
      // dirotta immagini su img field, il browser blocca input file fields
      if (['picture_1', 'picture_2', 'picture_3'].includes(key)) {
        if (value != null) {
          document.getElementById('spazi-' + key + '_view').src = value
          document.getElementById('spazi-' + key).value = ''
        } else {
          document.getElementById('spazi-' + key + '_view').src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAECAAAAACrNEBKAAAAJklEQVQI12P4DwUN/xugxP//LAzooJGBgQWVy8DAwFCPLFgPlwQAY6IUfqI3pB8AAAAASUVORK5CYII='
          document.getElementById('spazi-' + key).value = ''
        }
      } else if (key === 'fairness') {
        const fboxes = document.getElementsByClassName('fairness')
        for (const b of fboxes) {
          if (b.checked) { b.checked = false };
        };
        if ((value != null) && (value !== '')) {
          const values = value.split(', ')
          values.forEach(function (v) { document.getElementsByName(v.replace(' ', '_'))[0].checked = true })
        }
      } else if (key === 'resources') {
        const rboxes = document.getElementsByClassName('resources')
        for (const b of rboxes) {
          if (b.checked) { b.checked = false };
        };
        if ((value != null) && (value !== '')) {
          const values = value.split(', ')
          values.forEach(function (v) { document.getElementsByName(v.replace(' ', '_'))[0].checked = true })
        }
      } else {
        document.getElementById('spazi-' + key).value = value
      }
      if (readonly) {
        document.getElementById('spazi-' + key).readOnly = true
      }
    };
    if (key === 'description') {
      if (value === null) {
        editorP.content.innerHTML = ''
      } else {
        editorP.content.innerHTML = value
      };
    };
  }
  loadTalks(data.id)
  const OSMURL = 'https://www.openstreetmap.org/#map=18/' + data.latitude + '/' + data.longitude
  document.getElementById('spazi-linkOSM').setAttribute('href', OSMURL)
  const URIURL = apiUrl + '/records/spaces/' + data.id
  document.getElementById('spazi-linkURI').setAttribute('href', URIURL)
  const linkPP = data.participatory_process_link
  document.getElementById('spazi-linkPP').setAttribute('href', linkPP)
  const linkPDF = 'ptopdf.php?id=' + data.id
  document.getElementById('spazi-linkPDF').setAttribute('href', linkPDF)
  redrawOctosliderCanvas()
}

function parseISOString (s) {
  const b = s.split(/\D+/)
  return new Date(Date.UTC(b[0], --b[1], b[2], b[3], b[4], b[5], b[6]))
}

function updatePlace (marker, dataDict) {
  let datetimeNow = new Date()
  // check if datetime_last_edited is not null (to prevent errors of data coming from imports).
  if (marker.data.datetime_last_edited == null) {
    marker.data.datetime_last_edited = datetimeNow.toISOString()
  }
  if (marker.data.edited_by == null) {
    marker.data.edited_by = ''
  }
  const lastUpDate = parseISOString(marker.data.datetime_last_edited)
  const ms = lastUpDate.getTime() + 86400000
  const datetimeNewVersion = new Date(ms) // change version number only once per day
  const editors = marker.data.edited_by.split('\u{00A1}').includes(me.username) ? marker.data.edited_by : (marker.data.edited_by + '\u{00A1}' + me.username)
  if (datetimeNow > datetimeNewVersion) {
    datetimeNow = datetimeNow.toISOString()
    dataDict.datetime_last_edited = datetimeNow
    dataDict.version = marker.data.version + 1
  }
  dataDict.edited_by = editors
  for (const [key, value] of Object.entries(dataDict)) {
    marker.data[key] = value
  };
  return jca.update('spaces', marker.data.id,
    dataDict).catch(
    error => console.log(error)
  )
}

function placeToForeground (data) {
  fillPlaceFields(data)
  panelsHidden = false
  document.getElementById('cnvPlaceBox').style.display = 'block'
}

function userToForeground () {
  panelsHidden = false
  document.getElementById('cnvUserBox').style.display = 'block'
}

function authToForeground () {
  panelsHidden = false
  document.getElementById('cnvAuthBox').style.display = 'block'
}

function entrancesToForeground (data = null) {
  if (data != null) {
    fillEntranceFields(data)
  }
  panelsHidden = false
  document.getElementById('cnvEntrancesBox').style.display = 'block'
}

function allToBackground () {
  panelsHidden = true
  document.getElementById('blanket').style.display = 'none'
  document.getElementById('cnvPlaceBox').style.display = 'none'
  document.getElementById('cnvAuthBox').style.display = 'none'
  document.getElementById('cnvUserBox').style.display = 'none'
  document.getElementById('cnvEntrancesBox').style.display = 'none'
  if (entranceMarker !== undefined) {
    mpsMap.removeLayer(entranceMarker)
  }
}

function checkTerms () {
  if (document.register.terms.checked) {
    document.register.submitregistration.disabled = false
  } else {
    document.register.submitregistration.disabled = true
  }
}

function onUpdateDatafields (event) {
  const field = event.target.id.split('-')[1]
  updatePlace(activeMarker, { [field]: event.target.value })
}

function onUpdateListboxfields (event, cname) {
  // per ogni checbox, se selezionato, aggiungi a stringa con ", "(es: "disabilità, età, economiche") e invia a db.
  const boxes = document.getElementsByClassName(cname)
  let flist = ''
  for (const b of boxes) {
    if (b.checked) { flist += ', ' + b.name };
  }
  flist = flist.substr(2)
  updatePlace(activeMarker, { [cname]: flist })
}

// Define the onClick function for the filter button
function filterButtonClick () {
  // Perform filtering logic here
  console.log('Filter button clicked!')
}

// Define the onClick function for the filter button
function onEntrancesButtonClick () {
  document.getElementById('entrances-id').value = ''
  document.getElementById('entrances-inhabited_flats_count').value = -1
  document.getElementById('entrances-flats_count').value = -1
  entrancesToForeground()
  addEntrance()
}

function addEntrance () {
  const entranceIcon = L.icon({
    iconUrl: 'images/marker-icon_entrance.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    shadowUrl: 'images/marker-shadow.png',
    shadowSize: [41, 41]
  })
  entranceMarker = L.marker(mpsMap.getCenter(), { draggable: 'true', icon: entranceIcon }).addTo(mpsMap)
}

// eslint-disable-next-line no-unused-vars
function onFileSelected (event) {
  const selectedFile = event.target.files[0]
  const reader = new FileReader()
  const imgId = event.target.id + '_view'
  const imgAlt = event.target.id + '_alt'
  const imgtag = document.getElementById(imgId)
  imgtag.title = selectedFile.name
  imgtag.alt = document.getElementById(imgAlt).value
  reader.onload = function (event) {
    imgtag.src = event.target.result
  }
  reader.readAsDataURL(selectedFile)
  postImage(event)
}

function postImage (event) {
  const field = event.target.id.split('-')[1]
  // const ftype = event.target.files[0].name.split('.')[1]
  const r = (Math.random() + 1).toString(36).substring(5)
  let filename = me.id + '-' + r
  const dataImg = new FormData()
  dataImg.append('image', event.target.files[0])
  dataImg.append('filename', filename)

  fetch('/uploadImage.php', {
    method: 'POST',
    body: dataImg
  }).then(response => response.json())
    .then(function (data) {
      filename = data.name
      updatePlace(activeMarker, { [field]: data.name })
    }).catch(
      error => console.log(error)
    )
}

// radial octoslider monstruosity
const positions = [
  [[265, 170], [300, 155], [333, 140], [373, 123]],
  [[265, 226], [300, 240], [333, 253], [373, 270]],
  [[226, 265], [240, 300], [253, 333], [270, 373]],
  [[169, 265], [154, 300], [140, 333], [122, 373]],
  [[130, 226], [94, 240], [60, 253], [19, 270]],
  [[130, 170], [94, 155], [60, 140], [19, 122]],
  [[169, 130], [155, 94], [140, 60], [122, 19]],
  [[226, 130], [240, 94], [253, 60], [270, 19]]
]

function setOctoMiniature (event, isLabel) {
  let imgName
  if (isLabel) {
    imgName = event.target.nextElementSibling.id.split('-')[1]
  } else {
    imgName = event.target.id.split('-')[1]
  };
  document.getElementById('octomin').style.backgroundImage = 'url(./images/octomin/' + imgName + '.png)'
  document.getElementById('octodescription').innerHTML = octodescriptions[imgName]
}

const octomappings = { 0: 'physical_activity', 1: 'nature', 2: 'creativity', 3: 'conviviality', 4: 'care', 5: 'introspection', 6: 'citizenship', 7: 'learning' }

const octodescriptions = {
  physical_activity: '<b>Movimento:</b> per esempio camminare, fare sport, ballare.',
  nature: "<b>Natura:</b> osservare o interagire con la natura e prendersi cura dell'ambiente.",
  creativity: "<b>Pensiero critico e creatività:</b> La crescita di individui come soggetti autonomi e capaci di esprimere il loro pensiero critico. Per esempio confrontandosi su vari temi, la creazione o l'espressione artistica.",
  conviviality: "<b>Socializzazione:</b> L'integrazione degli individui nella società e nella cultura.",
  care: "<b>Cura:</b> L'espressione degli individui come soggetti capaci di cura.",
  introspection: '<b>Introspezione:</b> Processi di crescita personale basati sulla consapevolezza di sé, del proprio valore, delle proprie risorse.',
  citizenship: '<b>Attivismo:</b> attività civiche, politiche, culturali o sociali che generano un valore o che promuovono il benessere e lo sviluppo delle comunità.',
  learning: '<b>Formazione e memoria:</b> La trasmissione di conoscenze, abilità e della memoria collettiva. Per esempio lo studio in gruppo e la partecipazione ad attività educative.'
}

function relocateOctosliderLabels (p, index) {
  const label = document.getElementsByClassName('octolabel')[index]
  label.style.top = p[1] + 15 + 'px'
  label.style.left = p[0] - 25 + 'px'
}

function redrawOctosliderCanvas () {
  const ocanvas = document.getElementById('octamask')
  const octx = ocanvas.getContext('2d')
  octx.clearRect(0, 0, 400, 400)
  octx.beginPath()
  octx.arc(200, 200, 196, 0, 2 * Math.PI)
  octx.closePath()
  // draw shapes
  const offset = 3
  for (let i = 0; i < 8; i++) {
    const p = positions[i][activeMarker.data[octomappings[i]]]
    if (i === 0) {
      octx.moveTo(p[0] + offset, p[1] + offset)
    } else {
      octx.lineTo(p[0] + offset, p[1] + offset)
    }
    relocateOctosliderLabels(p, i)
  }
  octx.closePath()
  octx.fillStyle = '#DCE7D1d0'
  octx.fill('evenodd')
}

function setOctosliderCanvas (event) {
  const thumbNumber = event.target.classList[1].split('_')[1]
  const value = event.target.value
  // set the status
  activeMarker.data[octomappings[thumbNumber]] = parseInt(value)
  redrawOctosliderCanvas()
}

// eslint-disable-next-line no-unused-vars
function loadAmenities (dct) {
  for (const [key, value] of Object.entries(dct)) {
    console.log(key)
    L.circleMarker([value[0], value[1]]).addTo(mpsMap)
  }
}

// Function to close the popup
function closePopup() {
  document.getElementById("popupWelcomeBox").style.display = "none";
}


function showAlertDeleteUser()
  {
    let confirmMsg = 'Sicuro di voler cancellare l\'utente?'
    if (document.getElementById('DeleteUser').value !== confirmMsg) {
      document.getElementById('DeleteUser').value = confirmMsg
      return false
    } else {
      return true
    }
  }


// initialize events in window.onload
function todoOnload () {
  const wrapper = document.querySelector('.wrapper')
  const signupHeader = document.querySelector('.signup h2')
  const loginHeader = document.querySelector('.login h2')

  loginHeader.addEventListener('click', () => {
    wrapper.classList.add('active')
  })
  signupHeader.addEventListener('click', () => {
    wrapper.classList.remove('active')
  })

  const sldr = document.getElementsByClassName('octoslider')
  for (let i = 0; i < sldr.length; i++) {
    sldr[i].addEventListener('pointerup', function (e) {
      setOctosliderCanvas(e)
    })
    sldr[i].addEventListener('pointerdown', function (e) {
      setOctoMiniature(e, false)
    })
    sldr[i].addEventListener('focusout', function (e) {
      onUpdateDatafields(e)
    })
  }

  const flds = document.getElementsByClassName('fout')
  for (let i = 0; i < flds.length; i++) {
    flds[i].addEventListener('focusout', e => onUpdateDatafields(e))
  }

  // be sure to display an icon when clicking the label
  const sldrlbl = document.getElementsByClassName('spazi-meta-label octolabel')
  for (let i = 0; i < sldrlbl.length; i++) {
    sldrlbl[i].addEventListener('pointerdown', e => setOctoMiniature(e, true))
  }


  document.DelUser.addEventListener('submit', async event => {
    event.preventDefault()
    let lastChance = showAlertDeleteUser()
    if (lastChance === true) {
      fetch(
        apiUrl + '/me',
        {
          method: 'DELETE'
        }
      ).then(data => {
        window.location.reload();
      }
      ).catch(
        error => console.log(error)
      )
    }
  })
  
  document.getElementById('signupCheck').addEventListener('click', checkTerms)

  document.getElementById('collapse').addEventListener('pointerdown', toggleTabPanels)

  document.getElementById('spazi-fairness').addEventListener('focusout', function (e) {
    onUpdateListboxfields(e, 'fairness')
  })

  document.getElementById('spazi-resources').addEventListener('focusout', function (e) {
    onUpdateListboxfields(e, 'resources')
  })

  document.getElementById('spazi-vocation').addEventListener('focusout', function (e) {
    colorizeMarker(activeMarker)
  })

  const tabs = document.getElementsByName('tabset')
  for (let i = 0; i < tabs.length; i++) {
    tabs[i].nextElementSibling.addEventListener('pointerdown', function (e) { document.getElementsByClassName('tab-panels')[0].style.height = '60vh' })
  }

  document.getElementsByName('cancelEntrances')[0].addEventListener('click', allToBackground)

  document.register.addEventListener('submit', async event => {
    event.preventDefault()
    const data = new FormData(document.register)
    const formDataObj = {}
    data.forEach((value, key) => (formDataObj[key] = value))

    fetch(
      apiUrl + '/register',
      {
        method: 'POST',
        body: JSON.stringify(formDataObj)
      }
    ).then(response => response.json()
    ).then(data => registerMe(data)
    ).catch(
      error => registerError(error)
    )
  })
  
  document.posta.addEventListener('submit', async event => {
    event.preventDefault()
    if (document.posta[0].value === "") { return }
    let datetimeNow = new Date()
    datetimeNow = datetimeNow.toISOString()
    const data = new FormData(document.posta)
    const formDataObj = {
      datetime_created: datetimeNow,
      space_id: activeMarker.data.id,
      user_id: me.id,
      username: me.username,
      text: document.posta[0].value 
    }
    jca.create('talks', [
      formDataObj
    ]).catch(
      error => console.log(error)
    )
    document.posta[0].value = ""
    console.log(formDataObj)
    document.getElementById('talk').prepend(formatTalk(formDataObj))
  })

  //document.getElementById('cnvUserBox').addEventListener('click', function (e) {
    //document.getElementById('cnvUserBox').style.display = 'none'
  //})

  document.access.addEventListener('submit', async event => {
    event.preventDefault()
    jca.login(document.getElementById('loginUsername').value, document.getElementById('loginPassword').value).then(
      data => setMe(data)
    ).catch(
      error => loginError(error)
    )
  })

  document.entrances.addEventListener('submit', async event => {
    event.preventDefault()
    let latlng
    let nn
    if (entranceMarker !== undefined) {
      latlng = entranceMarker.getLatLng()
      nn = nearestNodeID([latlng.lat, latlng.lng], waysDataNtts)
    }
    const streetName = document.getElementById('entrances-street').value
    const streetNumber = document.getElementById('entrances-street_number').value
    const city = document.getElementById('entrances-city').value
    const id = document.getElementById('entrances-id').value
    const flatCount = document.getElementById('entrances-flats_count').value
    const inhabitedFlatCount = document.getElementById('entrances-inhabited_flats_count').value
    if (id === '') {
      createNewEntrance(latlng, streetName, streetNumber, city, flatCount, inhabitedFlatCount, nn)
    } else {
      updateEntrance(id, streetName, streetNumber, city, flatCount, inhabitedFlatCount)
    }
  })

  document.logout.addEventListener('submit', async event => {
    event.preventDefault()
    jca.logout().catch(
      error => console.log(error)
    )
    unsetMe()
  })

  document.Delete.addEventListener('submit', async event => {
    event.preventDefault()
    updatePlace(activeMarker, { deleted: 1 })
    // delete marker
    markers.removeLayer(activeMarker)
    nears.clearLayers()
    allToBackground()
  })

  editorP = window.pell.init({
    element: document.getElementById('editor'),
    actions: ['bold', 'italic', 'underline', 'strikethrough', 'heading3', 'heading4', 'paragraph', 'quote', 'link', 'image', 'olist', 'ulist', 'line'],
    defaultParagraphSeparator: 'p',
    onChange: function (html) {
      updatePlace(activeMarker, { description: html })
    }
  })
  
  if (localStorage.getItem('popupShown') === 'false') {
    closePopup()
  } else {
  document.getElementById("popupWelcomeBox").style.zIndex = '15'
  // Check if the "Don't show this again" checkbox is checked
  document.getElementById("noShow").addEventListener("change", function() {
    if (this.checked) {
      closePopup()
      // Store this state in localStorage to remember the user's choice
      localStorage.setItem("popupShown", "false")
    }
  })
  };

  const spinners = document.querySelectorAll('input[type="number"].spinner')
  spinners.forEach(function (spinner) {
    const decreaseDiv = document.createElement('div')
    decreaseDiv.textContent = '-'
    decreaseDiv.classList.add('decrease')
    decreaseDiv.addEventListener('click', function () {
      if (spinner.value >= 0) {
        spinner.value = parseInt(spinner.value) - 1
      }
    })
    spinner.parentNode.insertBefore(decreaseDiv, spinner)

    const increaseDiv = document.createElement('div')
    increaseDiv.textContent = '+'
    increaseDiv.classList.add('increase')
    if (spinner.id === 'entrances-flats_count') {
      increaseDiv.addEventListener('click', function () {
        spinner.value = parseInt(spinner.value) + 1
        spinner.parentElement.nextElementSibling.children[2].value = parseInt(spinner.parentElement.nextElementSibling.children[2].value) + 1
      })
    } else {
      increaseDiv.addEventListener('click', function () {
        spinner.value = parseInt(spinner.value) + 1
      })
    }
    spinner.parentNode.insertBefore(increaseDiv, spinner.nextSibling)
  })

  initMap()
}
