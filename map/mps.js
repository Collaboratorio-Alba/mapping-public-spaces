"use strict";

const api_url = "https://collab.42web.io/api/api.php";
const map_center = [44.6798, 8.0362];
const ways_data_file = "/data/nodes.json";
var ways_data_ntts = {};
var me;
var mpsMap;
var activeMarker;
var editorP;
const jcaconfigHeaders = {headers : {
    'Access-Control-Allow-Origin':'*',
    'Access-Control-Allow-Methods':'GET, POST, PUT, DELETE, PATCH, OPTIONS',
    'Access-Control-Allow-Headers':'Origin, X-Requested-With, Content, Accept, Content-Type, Authorization'}};
const jca=jscrudapi(api_url);
//markerCluster plugin
var markers = L.markerClusterGroup();
var panels_hidden = true;
let mapOptions = {
        preferCanvas: true,
        maxZoom: 19,
        minZoom: 9
    };

window.onload = (event) => {
    todoOnload();
    if (typeof me == 'undefined') {
        jca.me().then(
            data => setMe(data)
        ).catch (
            error=>loginError(error)
        );
    }
};

function loadJson(fname,variab) {
    fetch(fname)
    .then(response => response.json())
    .then(function(data){
        for (const [key, value] of Object.entries(data)) {
            variab[key] = value}
    }).catch (
        error=>console.log(error)
    );
}

function loadSpaces() {
    jca.list('spaces')
    .then(function(data){
        data['records'].forEach(function (item, index) {
            addPlace([parseFloat(item.latitude),parseFloat(item.longitude)],item);
        })
    }).catch (
        error=>console.log(error)
    );
}

function loadAmenities(dct) {
    for (const [key,value] of Object.entries(dct)) {
        L.circleMarker([value[0],value[1]]).addTo(mpsMap);
    }
}
    
function initMap() {
    mpsMap = L.map('mpsMap', mapOptions).setView(map_center, 14);
    const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>, <a href="mailto:ssh8lt5kr@mozmail.com?subject=segnalazione%20problema%20del%20sito%20collab.42web.io&body=Mentre%20usavo%20il%20sito%20ho%20riscontrato%20questo%20problema%3A%0A%0A%C3%A8%20successo%20mentre%20stavo...%0A%0Asto%20navigando%20da%3A%20telefono%2Fcomputer%0A%0Acon%20il%20browser%3A%20%0A">Segnala</a> un problema.'
    }).addTo(mpsMap);
    loadJson(ways_data_file, ways_data_ntts);
    mpsMap.on('click', mapClick);
    mpsMap.addLayer(markers);
    loadSpaces();
    mpsMap.on("click", allToBackground);
    const scale = L.control.scale().addTo(mpsMap);
    // create user button
    let userButtonElement = document.createElement('a');
    userButtonElement.innerHTML = '\u{1F464}';
    userButtonElement.setAttribute("href", "#");
    userButtonElement.className = 'leaflet-floating-button';
    userButtonElement.id = "usericon";
    L.DomEvent.disableClickPropagation(userButtonElement);
        L.DomEvent.on(userButtonElement, 'click', toggleUserPanel);
    // Add this leaflet control
    var buttonControl = L.Control.extend({
      options: {
        position: 'topright'
      },
  
      onAdd: function () {
        var container = L.DomUtil.create('div');
        container.appendChild(userButtonElement);
        return container;
      }
    });
    mpsMap.addControl(new buttonControl());
    
    if(L.Browser.mobile) {
        
    } else {

    };
};

function mapClick(e) {
    // also detect click on existing marker
    if (panels_hidden) {
        if ((typeof me !== 'undefined') && (me !== null)) {
            let name = prompt("Grazie per il tuo contributo! Vuoi aggiungere un luogo?\nSe hai dubbi controlla la guida all'uso toccando l'icona in alto a destra.\n\nCome si chiama questo spazio?");
            if ((name !== null) && (name !== "")) {
                addPlace([e.latlng.lat,e.latlng.lng],name);
            }
        }
    } else {
        allToBackground();
        activeMarker = null;
    }
}

async function addPlace(latlng,data) {
        var mar = L.marker(latlng,{draggable: 'true'}).on('click', onMarkerClick);
        if (typeof(data) === "string") {
            data = await createNewSpace(latlng, data)
            // reload from db
            jca.read('spaces', data
            ).then(function(resp){
                mar.data = resp;
            });            
        } else {
            mar.data = data;
        }
        mar.livedata = {};
        // add nearest node features to mar.data
        let nn = nearest_node_ID(latlng);
        mar.livedata.nearest_node_ID = nn[0];
        mar.livedata.nearest_node_dst = nn[1];
        mar.on('dragend', function(event){
            var marker = event.target;
            var position = marker.getLatLng();
            marker.setLatLng(position,{draggable:'true'}).update();
            // update record
            updatePlace(mar, {latitude: position.lat,
                 longitude: position.lng});
    });
        markers.addLayer(mar);     
}

function createNewSpace(latlng, named) {
    let datetimeNow = new Date();
    datetimeNow = datetimeNow.toISOString();
    return jca.create('spaces',[
        {name:named,
        latitude:latlng[0],
        longitude:latlng[1],
        version:0,
        datetime_created:datetimeNow,
        datetime_last_edited:datetimeNow,
        created_by:me['username'],
        edited_by:me['username']}
    ]).catch(
        error=>console.log(error)
    );
}

//marker interaction
function onMarkerClick(e) {
    activeMarker = this;
    placeToForeground(this.data);
    // here calculate, save and visualize spatial statistics.
}

function toggleUserPanel() {
    if (panels_hidden) {
        if (me == null) {
            allToBackground();
            authToForeground();
        } else {
            allToBackground();
            userToForeground();
        };
    } else {
        allToBackground();
    }
}

function loginError(e) {
    console.log(e);
    authToForeground();
    unsetMe();
    if (e['code'] == 1012) {
        document.getElementById("loginMessage").innerText = e['message'];
    }
}

function setMe(dati) {
    me = dati;
    document.getElementById("profileUsername").innerText = me["username"];
    document.getElementById("profileFirst_name").innerText = me["first_name"];
    document.getElementById("profileLast_name").innerText = me["last_name"];
    document.getElementById("profileEmail").innerText = me["email"];
    var fields = document.getElementsByClassName("ui");
    for(var i = 0; i < fields.length; i++) {
        fields[i].disabled = false;
    }
    document.getElementsByClassName("pell-content")[0].setAttribute('contenteditable',"true");
    document.getElementsByClassName("pell-actionbar")[0].style.display = 'block';
    allToBackground();
}

function unsetMe() {
    me = null;
    document.getElementById("profileUsername").innerText = "";
    document.getElementById("profileFirst_name").innerText = "";
    document.getElementById("profileLast_name").innerText = "";
    document.getElementById("profileEmail").innerText = "";
    var fields = document.getElementsByClassName("ui");
    for(var i = 0; i < fields.length; i++) {
        fields[i].disabled = true;
    }
    document.getElementsByClassName("pell-content")[0].setAttribute('contenteditable',"false");
    document.getElementsByClassName("pell-actionbar")[0].style.display = 'none';
    allToBackground();
}

function fillPlaceFields(data) {
    for (const [key, value] of Object.entries(data)) {
        if (!!document.getElementById("spazi-" + key)) {
            // dirotta immagini su img field, il browser blocca input file fields
            if (['picture_1', 'picture_2', 'picture_3'].includes(key)) {
                if (value != null) {
                    document.getElementById("spazi-" + key + "_view").src = value;
                    document.getElementById("spazi-" + key ).value = "";
                } else {
                    document.getElementById("spazi-" + key + "_view").src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAECAAAAACrNEBKAAAAJklEQVQI12P4DwUN/xugxP//LAzooJGBgQWVy8DAwFCPLFgPlwQAY6IUfqI3pB8AAAAASUVORK5CYII=";
                    document.getElementById("spazi-" + key ).value = "";
                }
            } else if (key === "fairness") {
                let fboxes = document.getElementsByClassName("fairness");
                for (let b of fboxes) {
                    if (b.checked) {b.checked = false};
                };
                if (value != null) {
                    let values = value.split(", ");
                    values.forEach((v) =>  document.getElementsByName(v.replace(" ", "_"))[0].checked = true); 
                } else {
                    for (let b of fboxes) {
                            if (b.checked) {b.checked = false};
                    }
                }
            } else if (key === "resources") {
                let rboxes = document.getElementsByClassName("resources");
                for (let b of rboxes) {
                    if (b.checked) {b.checked = false};
                };
                if (value != null) {
                    let values = value.split(", ");
                    values.forEach((v) =>  document.getElementsByName(v.replace(" ", "_"))[0].checked = true); 
                } else {
                    for (let b of rboxes) {
                            if (b.checked) {b.checked = false};
                    }
                }
            } else {
                document.getElementById("spazi-" + key).value = value;
            }
        };
        if (key === "description") {
            if (value === null ) {
                editorP.content.innerHTML = "";
            } else {
                editorP.content.innerHTML = value;
            };
        };
    }
    let OSMURL = 'https://www.openstreetmap.org/#map=18/'+data.latitude+'/'+data.longitude;
    document.getElementById("spazi-linkOSM").setAttribute('href', OSMURL);
    let URIURL = api_url + "/records/spaces/" + data.id;
    document.getElementById("spazi-linkURI").setAttribute('href', URIURL);
    let linkPP = data.participatory_process_link;
    document.getElementById("spazi-linkPP").setAttribute('href', linkPP);
    redrawOctosliderCanvas();
}

function parseISOString(s) {
    var b = s.split(/\D+/);
    return new Date(Date.UTC(b[0], --b[1], b[2], b[3], b[4], b[5], b[6]));
}

function updatePlace(marker, dataDict) {
    var datetimeNow = new Date();
    let lastUpDate = parseISOString(marker.data.datetime_last_edited)
    let ms = lastUpDate.getTime() + 86400000;
    let datetimeNewVersion = new Date(ms); // change version number only once per day
    let editors = marker.data.edited_by.split("\u{00A1}").includes(me.username) ? marker.data.edited_by : (marker.data.edited_by + "\u{00A1}" + me.username);
    if (datetimeNow > datetimeNewVersion) {
        datetimeNow = datetimeNow.toISOString();
        dataDict["datetime_last_edited"] = datetimeNow;
        dataDict["version"] = marker.data.version + 1;
    }
    dataDict["edited_by"] = editors;
    for (const [key, value] of Object.entries(dataDict)) {
        marker.data[key] = value};
    return jca.update('spaces', marker.data.id,
                dataDict).catch (
        error=>console.log(error)
    );
}

function placeToForeground(data) {
    fillPlaceFields(data);
    panels_hidden = false;
    document.getElementById("cnvPlaceBox").style.zIndex = "15";
}

function userToForeground() {
    panels_hidden = false;
    document.getElementById("cnvUserBox").style.zIndex = "15";
}

function authToForeground() {
    panels_hidden = false;
    document.getElementById("cnvAuthBox").style.zIndex = "15";
}

function allToBackground() {
    panels_hidden = true;
    document.getElementById("cnvPlaceBox").style.zIndex = "5";
    document.getElementById("cnvAuthBox").style.zIndex = "5";
    document.getElementById("cnvUserBox").style.zIndex = "5";
}

function checkTerms() {
    if(document.register.terms.checked)
    {
        document.register.submitregistration.disabled=false;
    }
    else
    {
        document.register.submitregistration.disabled=true;
    }
}

function onUpdateDatafields(event) {
    let field = event.target.id.split("-")[1]
    updatePlace(activeMarker, {[field]:event.target.value});
}

function onUpdateListboxfields(event,cname) {
    // per ogni checbox, se selezionato, aggiungi a stringa con ", "(es: "disabilità, età, economiche") e invia a db.
    let boxes = document.getElementsByClassName(cname);
    var flist = "";
    for (let b of boxes) {
        if (b.checked) {flist += ", " + b.name };
    }
    flist = flist.substr(2);
    updatePlace(activeMarker, {[cname]:flist});
}

function onFileSelected(event) {
    var selectedFile = event.target.files[0];
    var reader = new FileReader();
    let imgId = event.target.id + "_view";
    let imgAlt = event.target.id + "_alt";
    var imgtag = document.getElementById(imgId);
    imgtag.title = selectedFile.name;
    imgtag.alt = document.getElementById(imgAlt).value;
    reader.onload = function(event) {
        imgtag.src = event.target.result;
    };
    reader.readAsDataURL(selectedFile);
    postImage(event);
}

function postImage(event) {
    let field = event.target.id.split("-")[1];
    let ftype = event.target.files[0].name.split(".")[1]
    let r = (Math.random() + 1).toString(36).substring(5);
    let filename = me.id + "-" + r;
    var dataImg = new FormData();
    dataImg.append('image', event.target.files[0]);
    dataImg.append('filename', filename);

    fetch('/uploadImage.php', {
        method: 'POST',
        body: dataImg
    }).then(response => response.json())
    .then(function(data){
        filename = data.name;
        updatePlace(activeMarker, {[field]:data.name});
    }).catch (
        error=>console.log(error)
    );
}

// avoiding Dealunay tessellation, finds the nearest node
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
        n_filt = filter_bbox(ways_data_ntts,latlon,xspan,yspan);
        xspan += 0.0005;
        yspan += 0.00065;
    }
    return n_filt;
}

// Functiion to filter points inside a bounding box square centered at a point
function filter_bbox(dct, pnt, extentx, extenty) {
    let pmin = {x:(pnt[0] - extentx), y:(pnt[1] - extenty)};
    let pmax = {x:(pnt[0] + extentx), y:(pnt[1] + extenty)};
    //return Object.assign({},Object.entries(dct).filter(([k,el]) => el[0] > pmin.x && el[0] < pmax.x && el[1] > pmin.y && el[1] < pmax.y).map(([k,v]) => ({[k]:v})));
    return Object.assign({}, ... Object.entries(ways_data_ntts).filter((en) => en[1][0] > pmin.x && en[1][0] < pmax.x && en[1][1] > pmin.y && en[1][1] < pmax.y).map(([k,v]) => ({[k]:v})))
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


// radial octoslider monstruosity
const positions = [
    [[265,170],[300,155],[333,140],[373,123]],
    [[265,226],[300,240],[333,253],[373,270]],
    [[226,265],[240,300],[253,333],[270,373]],
    [[169,265],[154,300],[140,333],[122,373]],
    [[130,226],[94,240],[60,253],[19,270]],
    [[130,170],[94,155],[60,140],[19,122]],
    [[169,130],[155,94],[140,60],[122,19]],
    [[226,130],[240,94],[253,60],[270,19]]
];

function setOctoMiniature(event, isLabel) {
    let imgName;
    if (isLabel) {
        imgName = event.target.nextElementSibling.id.split("-")[1];
    } else {
        imgName = event.target.id.split("-")[1];
    };
    document.getElementById("octomin").style.backgroundImage = 'url(./images/octomin/' + imgName + '.png)';
    document.getElementById("octodescription").innerHTML = octodescriptions[imgName];
}

const octomappings = {0:"physical_activity",1:"nature",2:"creativity",3:"conviviality",4:"care",5:"contemplation",6:"citizenship",7:"learning"};

const octodescriptions = {
    "physical_activity":"<b>Movimento:</b> per esempio camminare, fare sport, ballare e altre forme di esercizio fisico. ",
    "nature":"<b>Natura:</b> interazione con la natura e cura dell'ambiente: osservare, identificare o interagire con la natura e prendersi cura dell'ambiente.",
    "creativity":"<b>Creatività:</b> per esempio la creazione o l'espressione artistica, come recitare, partecipare a laboratori artistici, stare assieme per costruire opere artigianali o fare musica. ",
    "conviviality":"<b>Comunità:</b> per esempio mangiare e bere assieme e altre esperienze piacevoli con gli altri, come organizzare feste, riunioni o giocare. ",
    "care":"<b>Cura:</b> per esempio il volontariato, fornire assistenza sanitaria o personale, assistenza all'infanzia o agli anziani e fornire supporto a persone in situazioni di crisi o emergenza.",
    "contemplation":"<b>Contemplazione:</b> per esempio attività come preghiera, meditazione, consapevolezza e indagine filosofica o spirituale.",
    "citizenship":"<b>Attivismo:</b> lavoro di comunità, per esempio attività civiche, politiche o sociali che generano un valore o che promuovono il benessere e lo sviluppo delle comunità, come la partecipazione a processi di rinnovamento sociale e urbano, il volontariato politico sociale, l'attivismo e il sostegno.",
    "learning":"<b>Educazione:</b> per esempio lo studio in gruppo, i gruppi di lettura e la partecipazione ad attività educative."
    };

function relocateOctosliderLabels(p,index) {
    let label = document.getElementsByClassName("octolabel")[index];
    label.style.top = p[1]+15 + 'px';
    label.style.left = p[0]-25 + 'px';
}

function redrawOctosliderCanvas() {
    const ocanvas = document.getElementById("octamask");
    const octx = ocanvas.getContext("2d");
    octx.clearRect(0, 0, 400, 400);
    octx.beginPath();
    octx.arc(200, 200, 196, 0, 2 * Math.PI);
    octx.closePath();
    // draw shapes
    let offset = 3
    for (let i = 0; i < 8; i++) {
        let p = positions[i][activeMarker.data[octomappings[i]]];
        if (i == 0) {
            octx.moveTo(p[0] + offset, p[1] + offset);
        } else {
            octx.lineTo(p[0] + offset, p[1] + offset);
        }
        relocateOctosliderLabels(p,i);
    }
    octx.closePath();
    octx.fillStyle = "#DCE7D1d0";
    octx.fill('evenodd');
}

function setOctosliderCanvas(event) {
    let thumbNumber = event.target.classList[1].split("_")[1];
    let value = event.target.value;
    // set the status
    activeMarker.data[octomappings[thumbNumber]] = parseInt(value);
    redrawOctosliderCanvas();
}

//initialize events in window.onload
function todoOnload() {
    let sldr = document.getElementsByClassName("octoslider");
    for (let i = 0; i < sldr.length; i++){
        sldr[i].addEventListener('pointerup', function(e) {
                setOctosliderCanvas(e);
        });
        sldr[i].addEventListener('pointerdown', function(e) {
                setOctoMiniature(e, false);
        });
        sldr[i].addEventListener('focusout', function(e) {
                onUpdateDatafields(e);
        });
    }
    // be sure to display an icon when clicking the label
    let sldrlbl = document.getElementsByClassName("spazi-meta-label octolabel");
    for (let i = 0; i < sldrlbl.length; i++){
        sldrlbl[i].addEventListener('pointerdown', e => setOctoMiniature(e, true));
    }
    
    document.register.addEventListener('submit', async event => {
        event.preventDefault();
        
        const data = new FormData(document.register);
        const formDataObj = {};
        data.forEach((value, key) => (formDataObj[key] = value));
        
        try {
        const res = await fetch(
          api_url + '/register',
          {
            method: 'POST',
            body: JSON.stringify(formDataObj),
          },
        );
        const resData = await res.json();
        console.log(resData);
        } catch (err) {
        console.log(err.message);
        }
    });
    
    document.getElementById("cnvUserBox").addEventListener('click', function(e) {
            document.getElementById("cnvUserBox").style.zIndex = "5";
        });
    
    document.access.addEventListener('submit', async event => {
        event.preventDefault();
        jca.login(document.getElementById("loginUsername").value,document.getElementById("loginPassword").value).then(
            data => setMe(data)
        ).catch (
            error=>loginError(error)
        );
    });
    
    document.logout.addEventListener('submit', async event => {
        event.preventDefault();
        jca.logout().then(
            unsetMe()
        ).catch (
            error=>console.log(error)
        );
    });
    
    editorP = window.pell.init({
        element: document.getElementById('editor'),
        actions: ['bold', 'italic', 'underline', 'strikethrough', 'heading3', 'heading4', 'paragraph', 'quote', 'link', 'image', 'olist', 'ulist', 'line'],
        defaultParagraphSeparator: 'p',
        onChange: function (html) {
          //document.getElementById('text-output').innerHTML = html //document.getElementById('html-output').textContent = html
          // update both the local marker data and the server field
          updatePlace(activeMarker, {"description":html});
        }
      })    

    initMap();
}
