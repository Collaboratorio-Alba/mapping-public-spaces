'use strict'

/* global jscrudapi, L, nearestNodeID, findIsodistancePolyline, findConvexHull, areaOfPolygons */

const apiUrl = 'https://collab.42web.io/api/api.php'
const mapCenter = [44.6798, 8.0362]
const waysDataFile = '/data/nodes.json'
const waysDataNtts = {}
let me
let mpsMap
let activeMarker
let editorP
const pedonalDistance15 = 1250 // @ 5km/h , t=15min. Distance of a 15 minute walk
// const jcaconfigHeaders = {headers : {
// 'Access-Control-Allow-Origin':'*',
// 'Access-Control-Allow-Methods':'GET, POST, PUT, DELETE, PATCH, OPTIONS',
// 'Access-Control-Allow-Headers':'Origin, X-Requested-With, Content, Accept, Content-Type, Authorization'}};

/**
 * Represents a JavaScript CRUD API client for performing CRUD operations on a specific API endpoint.
 *
 * @param {string} apiUrl - The URL of the API endpoint to interact with.
 * @returns {Object} An instance of the jscrudapi client.
 */
const jca = jscrudapi(apiUrl)

// markerCluster plugin
const markers = L.markerClusterGroup({ disableClusteringAtZoom: 16 })
const nears = L.markerClusterGroup()
let panelsHidden = true
const mapOptions = {
  preferCanvas: true,
  maxZoom: 19,
  minZoom: 9
}

window.onload = (event) => {
  if (typeof me === 'undefined') {
    jca.me().then(
      data => setMe(data)
    ).catch(
      error => loginError(error)
    )
  }
  todoOnload()
}

function initMap () {
  mpsMap = L.map('mpsMap', mapOptions).setView(mapCenter, 14)
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>, <a href="mailto:ssh8lt5kr@mozmail.com?subject=segnalazione%20problema%20del%20sito%20collab.42web.io&body=Mentre%20usavo%20il%20sito%20ho%20riscontrato%20questo%20problema%3A%0A%0A%C3%A8%20successo%20mentre%20stavo...%0A%0Asto%20navigando%20da%3A%20telefono%2Fcomputer%0A%0Acon%20il%20browser%3A%20%0A">Segnala</a> un problema.'
  }).addTo(mpsMap)
  mpsMap.on('click', mapClick)
  mpsMap.addLayer(markers)
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
  mpsMap.addControl(new UserControl())
  mpsMap.addControl(new FilterControl())
  // if(L.Browser.mobile) {
  // } else {
  // };
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
    // unserialize
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
  markers.addLayer(mar)
}

// draw but do not show
function drawIsochrone (marker) {
  const latlng = [parseFloat(marker.data.latitude), parseFloat(marker.data.longitude)]
  const nn = nearestNodeID(latlng, waysDataNtts)
  // detect missing data or moved node this solution is prone to missing updates in case of markers far away from nodes
  if ((nn[0] !== marker.data.nearest_node_ID) || (Number.isNaN(marker.data.isochrone_area5))) {
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
  // add marker to nearest node's list of near relevant markers
  if (waysDataNtts[marker.data.nearest_node_ID].length === 3) {
    waysDataNtts[marker.data.nearest_node_ID].push([])
  }
  if (!waysDataNtts[marker.data.nearest_node_ID][3].includes(marker)) {
    waysDataNtts[marker.data.nearest_node_ID][3].push(marker)
  }
}

function recomputeDistances (marker, nearNode) {
  // if node has this marker in neighbours list, remove it.
  if ((marker.data.nearest_node_ID != null) && (marker.data.nearest_node_ID in waysDataNtts) && (waysDataNtts[marker.data.nearest_node_ID].lenght === 3)) {
    const markerIndex = waysDataNtts[marker.data.nearest_node_ID][3].indexOf(marker)
    if (markerIndex > -1) {
      waysDataNtts[marker.data.nearest_node_ID][3].splice(markerIndex, 1)
      console.log('removing reference to this marker')
    }
  }
  marker.data.nearest_node_ID = nearNode[0]
  marker.data.nearest_node_dst = nearNode[1]
  const isodista = findIsodistancePolyline(waysDataNtts, marker.data.nearest_node_ID, pedonalDistance15, marker.data.nearest_node_dst)
  marker.data.isochrone15_latlngs = findConvexHull(isodista[2])
  marker.data.isochrone10_latlngs = findConvexHull(isodista[1])
  marker.data.isochrone5_latlngs = findConvexHull(isodista[0])
  const areas51015 = areaOfPolygons([marker.data.isochrone5_latlngs, marker.data.isochrone10_latlngs, marker.data.isochrone15_latlngs])
  marker.data.isochrone_area5 = parseInt(areas51015[0])
  marker.data.isochrone_area10 = parseInt(areas51015[1])
  marker.data.isochrone_area15 = parseInt(areas51015[2])
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
    document.getElementsByClassName('tab-panels')[0].style.height = '70vh'
  } else {
    document.getElementsByClassName('tab-panels')[0].style.height = '0vh'
  };
}

function loginError (e) {
  console.log(e)
  authToForeground()
  unsetMe()
  if (e.code === 1012) {
    document.getElementById('loginMessage').innerText = e.message
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
  markers.eachLayer(function (l) {
    if (Object.prototype.hasOwnProperty.call(l, 'dragging')) {
      l.dragging.enable()
    }
  })
  allToBackground()
}

function unsetMe () {
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
  markers.eachLayer(function (l) {
    if (Object.prototype.hasOwnProperty.call(l, 'dragging')) {
      l.dragging.disable()
    }
  })
  allToBackground()
}

function fillPlaceFields (data) {
  for (const [key, value] of Object.entries(data)) {
    if (document.getElementById('spazi-' + key)) {
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
        if (value != null) {
          const values = value.split(', ')
          values.forEach(function (v) { document.getElementsByName(v.replace(' ', '_'))[0].checked = true })
        }
      } else if (key === 'resources') {
        const rboxes = document.getElementsByClassName('resources')
        for (const b of rboxes) {
          if (b.checked) { b.checked = false };
        };
        if (value != null) {
          const values = value.split(', ')
          values.forEach(function (v) { document.getElementsByName(v.replace(' ', '_'))[0].checked = true })
        }
      } else {
        document.getElementById('spazi-' + key).value = value
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
  document.getElementById('cnvPlaceBox').style.zIndex = '15'
}

function userToForeground () {
  panelsHidden = false
  document.getElementById('cnvUserBox').style.zIndex = '15'
}

function authToForeground () {
  panelsHidden = false
  document.getElementById('cnvAuthBox').style.zIndex = '15'
}

function allToBackground () {
  panelsHidden = true
  document.getElementById('cnvPlaceBox').style.zIndex = '5'
  document.getElementById('cnvAuthBox').style.zIndex = '5'
  document.getElementById('cnvUserBox').style.zIndex = '5'
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

const octomappings = { 0: 'physical_activity', 1: 'nature', 2: 'creativity', 3: 'conviviality', 4: 'care', 5: 'contemplation', 6: 'citizenship', 7: 'learning' }

const octodescriptions = {
  physical_activity: '<b>Movimento:</b> per esempio camminare, fare sport, ballare e altre forme di esercizio fisico. ',
  nature: "<b>Natura:</b> interazione con la natura e cura dell'ambiente: osservare, identificare o interagire con la natura e prendersi cura dell'ambiente.",
  creativity: "<b>Creatività:</b> per esempio la creazione o l'espressione artistica, come recitare, partecipare a laboratori artistici, stare assieme per costruire opere artigianali o fare musica. ",
  conviviality: '<b>Comunità:</b> per esempio mangiare e bere assieme e altre esperienze piacevoli con gli altri, come organizzare feste, riunioni o giocare. ',
  care: "<b>Cura:</b> per esempio il volontariato, fornire assistenza sanitaria o personale, assistenza all'infanzia o agli anziani e fornire supporto a persone in situazioni di crisi o emergenza.",
  contemplation: '<b>Contemplazione:</b> per esempio attività come preghiera, meditazione, consapevolezza e indagine filosofica o spirituale.',
  citizenship: "<b>Attivismo:</b> progetti e lavoro di comunità, per esempio attività civiche, politiche o sociali che generano un valore o che promuovono il benessere e lo sviluppo delle comunità, come la partecipazione a processi di rinnovamento sociale e urbano, il volontariato politico sociale, l'attivismo e il sostegno.",
  learning: '<b>Educazione:</b> per esempio lo studio in gruppo, i gruppi di lettura e la partecipazione ad attività educative.'
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

function loadJson (fname, variab) {
  fetch(fname)
    .then(response => response.json())
    .then(function (data) {
      for (const [key, value] of Object.entries(data)) {
        variab[key] = value
      }
    }).catch(
      error => console.log(error)
    )
}

// eslint-disable-next-line no-unused-vars
function loadAmenities (dct) {
  for (const [key, value] of Object.entries(dct)) {
    console.log(key)
    L.circleMarker([value[0], value[1]]).addTo(mpsMap)
  }
}

// initialize events in window.onload
function todoOnload () {
  loadJson(waysDataFile, waysDataNtts)

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

  document.getElementById('signupCheck').addEventListener('click', checkTerms)

  document.getElementById('collapse').addEventListener('pointerdown', toggleTabPanels)

  document.getElementById('spazi-fairness').addEventListener('focusout', function (e) {
    onUpdateListboxfields(e, 'fairness')
  })

  document.getElementById('spazi-resources').addEventListener('focusout', function (e) {
    onUpdateListboxfields(e, 'resources')
  })

  const tabs = document.getElementsByName('tabset')
  for (let i = 0; i < tabs.length; i++) {
    tabs[i].nextElementSibling.addEventListener('pointerdown', function (e) { document.getElementsByClassName('tab-panels')[0].style.height = '70vh' })
  }

  document.register.addEventListener('submit', async event => {
    event.preventDefault()

    const data = new FormData(document.register)
    const formDataObj = {}
    data.forEach((value, key) => (formDataObj[key] = value))

    try {
      const res = await fetch(
        apiUrl + '/register',
        {
          method: 'POST',
          body: JSON.stringify(formDataObj)
        }
      )
      const resData = await res.json()
      console.log(resData)
    } catch (err) {
      console.log(err.message)
    }
  })

  document.getElementById('cnvUserBox').addEventListener('click', function (e) {
    document.getElementById('cnvUserBox').style.zIndex = '5'
  })

  document.access.addEventListener('submit', async event => {
    event.preventDefault()
    jca.login(document.getElementById('loginUsername').value, document.getElementById('loginPassword').value).then(
      data => setMe(data)
    ).catch(
      error => loginError(error)
    )
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

  initMap()
}
