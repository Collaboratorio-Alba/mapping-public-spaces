<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title>Public or vacant spaces map</title>
    <link rel="icon" href="favicon.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src=" https://cdn.jsdelivr.net/npm/regenerator-runtime@0.13.11/runtime.min.js "></script>
    <link rel="stylesheet" href="map/MarkerCluster.css">
    <link rel="stylesheet" href="map/MarkerCluster.Default.css">
    <script src="map/leaflet.markercluster.js"></script>
    <script src="api/js-crud-api-min.js"></script>
    <script src="vendor/pell.min.js"></script>
    <link rel="stylesheet" type="text/css" href="vendor/pell.min.css">
    <link rel="stylesheet" type="text/css" href="map/mps.css">
    <link rel="stylesheet" type="text/css" href="map/radial-octoslider-monstruosity.css">
    <script src="map/mps.js"></script>
</head>
<body>
<div id="blanket"></div>
<div id="cnvPlaceBox">
    <section class="wrapperPlace">
        <div class="profile" id="mpsPlace">
            <input type="text" class="form-control ui" name="spazi-name" id="spazi-name" onfocusout="onUpdateDatafields(event)">
            <div class="tabset">
              <!-- Tab 1 -->
              <input type="radio" name="tabset" id="tab1" aria-controls="funzione" checked>
              <label for="tab1">&#8984;</label>
              <!-- Tab 2 -->
              <input type="radio" name="tabset" id="tab2" aria-controls="uso">
              <label for="tab2">&#9287;</label>
              <!-- Tab 3 -->
              <input type="radio" name="tabset" id="tab3" aria-controls="utenza">
              <label for="tab3">&#161;&#161;&#161;</label>
              <!-- Tab 4 -->
              <input type="radio" name="tabset" id="tab4" aria-controls="partecipazione">
              <label for="tab4">&#164;</label>
              <!-- Tab 5 -->
              <input type="radio" name="tabset" id="tab5" aria-controls="interviews">
              <label for="tab5">&#8264;</label>  
              <!-- Tab 6 -->
              <input type="radio" name="tabset" id="tab6" aria-controls="foto">
              <label for="tab6">&#128247;</label>  
              <!-- Tab 7 -->
              <input type="radio" name="tabset" id="tab7" aria-controls="meta">
              <label for="tab7">&#128203;</label>          
              <div class="tab-panels">
                <section id="funzione" class="tab-panel">
                  <h2>Funzione, caratteristiche</h2>
                  <!-- Rich text editor Pell -->
                  <div id="editor" class="pell"></div>
                  <!-- risorse -->
                  <div class="checkbox">
                      <fieldset id="spazi-resources" onfocusout="onUpdateListboxfields(event,'resources')">
                      <legend>risorse:</legend>
                        <span><input type="checkbox" class="resources ui" name="locali_interni" value="locali_interni" />
                        <label for="disabilità">locali interni</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="spazi_esterni" value="spazi_esterni" />
                        <label for="spazi_esterni">spazi esterni pedonali</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="verde" value="verde" />
                        <label for="verde">spazi nel verde</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="orti" value="orti" />
                        <label for="orti">orti</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="calcetto_pingpong" value="calcetto_pingpong" />
                        <label for="calcetto_pingpong">calcetto pingpong</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="area_gioco" value="area_gioco" />
                        <label for="area_gioco">area gioco bimbi</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="campo_sportivo" value="campo_sportivo" />
                        <label for="campo_sportivo">campo sportivo</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="spogliatoi" value="spogliatoi" />
                        <label for="spogliatoi">spogliatoi/docce</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="palestra" value="palestra" />
                        <label for="palestra">palestra</label></span>

                        <span><input type="checkbox" class="resources ui" name="servizi" value="servizi" />
                        <label for="servizi">servizi igienici</label></span>

                        <span><input type="checkbox" class="resources ui" name="acqua" value="acqua" />
                        <label for="acqua">acqua potabile</label></span>

                        <span><input type="checkbox" class="resources ui" name="fasciatoio" value="fasciatoio" />
                        <label for="fasciatoio">fasciatoio</label></span>

                        <span><input type="checkbox" class="resources ui" name="ambulatorio" value="ambulatorio" />
                        <label for="ambulatorio">ambulatorio infermieristico</label></span>

                        <span><input type="checkbox" class="resources ui" name="preghiera" value="preghiera" />
                        <label for="preghiera">luogo di preghiera</label></span>

                        <span><input type="checkbox" class="resources ui" name="cucina" value="cucina" />
                        <label for="cucina">cucina comunitaria</label></span>

                        <span><input type="checkbox" class="resources ui" name="caffetteria" value="caffetteria" />
                        <label for="caffetteria">caffetteria</label></span>

                        <span><input type="checkbox" class="resources ui" name="mensa" value="mensa" />
                        <label for="mensa">mensa</label></span>

                        <span><input type="checkbox" class="resources ui" name="barbecue" value="barbecue" />
                        <label for="barbecue">cucina esterna/barbecue</label></span>

                        <span><input type="checkbox" class="resources ui" name="sala_feste" value="sala_feste" />
                        <label for="sala_feste">sala polifunzionale</label></span>

                        <span><input type="checkbox" class="resources ui" name="tavoli_e_sedie" value="tavoli_e_sedie" />
                        <label for="tavoli_e_sedie">tavoli e sedie</label></span>

                        <span><input type="checkbox" class="resources ui" name="WiFi" value="WiFi" />
                        <label for="WiFi">WiFi libero</label></span>

                        <span><input type="checkbox" class="resources ui" name="PC" value="PC" />
                        <label for="PC">postazioni informatiche</label></span>

                        <span><input type="checkbox" class="resources ui" name="ludoteca" value="ludoteca" />
                        <label for="ludoteca">ludoteca</label></span>

                        <span><input type="checkbox" class="resources ui" name="oggettoteca" value="oggettoteca" />
                        <label for="oggettoteca">oggettoteca</label></span>

                        <span><input type="checkbox" class="resources ui" name="biblioteca" value="biblioteca" />
                        <label for="biblioteca">biblioteca</label></span>

                        <span><input type="checkbox" class="resources ui" name="quotidiani" value="quotidiani" />
                        <label for="quotidiani">lettura quotidiani</label></span>

                        <span><input type="checkbox" class="resources ui" name="museo" value="museo" />
                        <label for="museo">museo</label></span>

                        <span><input type="checkbox" class="resources ui" name="laboratorio" value="laboratorio" />
                        <label for="laboratorio">laboratorio arti mestieri/fablab</label></span>

                        <span><input type="checkbox" class="resources ui" name="teatro" value="teatro" />
                        <label for="teatro">teatro o anfiteatro</label></span>

                        <span><input type="checkbox" class="resources ui" name="proiezioni" value="proiezioni" />
                        <label for="proiezioni">area per proiezioni</label></span>

                        <span><input type="checkbox" class="resources ui" name="letti" value="letti" />
                        <label for="letti">posti letto</label></span>

                        <span><input type="checkbox" class="resources ui" name="appartamento" value="appartamento" />
                        <label for="appartamento">appartamento</label></span>
                        
                        <div class="spazi-meta form-group">
                            <label for="spazi-other_resources" class="spazi-meta-label">altro: </label>
                            <input type="text" class="form-control ui" name="spazi-other_resources" id="spazi-other_resources" onfocusout="onUpdateDatafields(event)">
                        </div>

                      </fieldset>
                  </div>
<!--
                    condizioni delle strutture
-->
                  <div class="field-select">
                    <label for="spazi-concomitance" class="spazi-meta-label">condizioni delle strutture e risorse disponibili</label>
                    <select class="form-control ui" name="spazi-concomitance" id="spazi-concomitance" onfocusout="onUpdateDatafields(event)">
                        <option value=" "></option>
                        <option value="pessime">pessime</option>
                        <option value="trascurate">trascurate</option>
                        <option value="buone">buone</option>
                    </select>
                  </div>
  
              </section>
                <section id="uso" class="tab-panel">
                  <h2>Uso, condizioni, cenni storici</h2>
                  <div class="field-select">
                    <label for="spazi-lifecycle_status" class="spazi-meta-label">stato attuale: </label>
                    <select class="form-control ui" name="spazi-lifecycle_status" id="spazi-lifecycle_status" onfocusout="onUpdateDatafields(event)">
                        <option value="cantiere">cantiere</option>
                        <option value="utilizzato">utilizzato</option>
                        <option value="inutilizzato">inutilizzato</option>
                        <option value="abbandonato">abbandonato</option>
                    </select>
                  </div>
                  <div class="field-select">
                    <label for="spazi-operator_category" class="spazi-meta-label">gestito da ente: </label>
                    <select class="form-control ui" name="spazi-operator_category" id="spazi-operator_category" onfocusout="onUpdateDatafields(event)">
                        <option value="privato">privato</option>
                        <option value="pubblico">pubblico</option>
                        <option value="ETS">ETS</option>
                        <option value="società">società</option>
                        <option value="società">informale</option>
                        <option value="abbandonato">nessuno</option>
                    </select>
                  </div>
                  <div class="spazi-meta form-group">
                        <label for="spazi-operator" class="spazi-meta-label">gestore: </label>
                        <input type="text" class="form-control ui" name="spazi-operator" id="spazi-operator" onfocusout="onUpdateDatafields(event)">
                  </div>
                  <label for="spazi-use" class="spazi-meta-label">osservazioni</label>
                  <textarea rows="8" cols="38" class="form-control ui" name="spazi-use" id="spazi-use" onfocusout="onUpdateDatafields(event)"></textarea>
                </section>
                <section id="utenza" class="tab-panel">
                  <h2>Bacino d'utenza, accessibilità</h2>
                  <!-- accesso -->
                  <div class="field-select">
                    <label for="spazi-access" class="spazi-meta-label">accesso: </label>
                    <select class="form-control ui" name="spazi-access" id="spazi-access" onfocusout="onUpdateDatafields(event)">
                        <option value="privato">privato</option>
                        <option value="libero">libero</option>
                        <option value="libero">inaccessibile</option>
                        <option value="oneroso">oneroso</option>
                        <option value="soci">soci</option>
                        <option value="residenti">residenti</option>
                    </select>
                  </div>
                  <label for="spazi-accessibility" class="spazi-meta-label">considerazioni</label>
                  <textarea rows="8" cols="38" class="form-control ui" name="spazi-accessibility" id="spazi-accessibility" onfocusout="onUpdateDatafields(event)"></textarea>
                  <!-- equità -->
                  <div class="checkbox">
                      <fieldset id="spazi-fairness" onfocusout="onUpdateListboxfields(event,'fairness')">
                      <legend>non equità rispetto a...</legend> <!-- disabilità, età, economiche, lingua, orientamento sessuale, dipendenze, etnia, religione, nazionalità o geografica, condizione sociale, convinzione politica -->
                        <span><input type="checkbox" class="fairness ui" id="disabilità" name="disabilità" value="disabilità" />
                        <label for="disabilità">disabilità</label></span>

                        <span><input type="checkbox" class="fairness ui" name="età" value="età" />
                        <label for="età">età</label></span>

                        <span><input type="checkbox" class="fairness ui" name="economiche" value="economiche" />
                        <label for="lingua">condizioni economiche</label></span>

                        <span><input type="checkbox" class="fairness ui" name="lingua" value="lingua" />
                        <label for="lingua">lingua</label></span>

                        <span><input type="checkbox" class="fairness ui" name="sessuale" value="sessuale" />
                        <label for="sessuale">genere o sessuale</label></span>

                        <span><input type="checkbox" class="fairness ui" name="dipendenze" value="dipendenze" />
                        <label for="dipendenze">dipendenze</label></span>

                        <span><input type="checkbox" class="fairness ui" name="etnia" value="etnia" />
                        <label for="etnia">etnia</label></span>

                        <span><input type="checkbox" class="fairness ui" name="religione" value="religione" />
                        <label for="religione">religione</label></span>

                        <span><input type="checkbox" class="fairness ui" name="geografica" value="geografica" />
                        <label for="geografica">origine geografica</label></span>

                        <span><input type="checkbox" class="fairness ui" name="politica" value="politica" />
                        <label for="politica">idee politiche</label></span>

                      </fieldset>
                  <label for="spazi-fairness_description" class="spazi-meta-label">descrizione delle iniquità</label>
                  <textarea rows="3" cols="38" class="form-control ui" name="spazi-fairness_description" id="spazi-fairness_description" onfocusout="onUpdateDatafields(event)"></textarea>
                  </div>
                </section>
                <section id="partecipazione" class="tab-panel">
                  <h2>Opportunità di partecipazione</h2>
                    <figure class="slidercontainer">
                        <div class="octowrapper" id="octoslider">
                            <label for="spazi-physical_activity" class="spazi-meta-label octolabel">movimento</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider one_0 ui" id="spazi-physical_activity">
                            <label for="spazi-nature" class="spazi-meta-label octolabel">natura</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider two_1 ui" id="spazi-nature">
                            <label for="spazi-creativity" class="spazi-meta-label octolabel">creatività</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider three_2 ui" id="spazi-creativity">
                            <label for="spazi-conviviality" class="spazi-meta-label octolabel">comunità</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider four_3 ui" id="spazi-conviviality">
                            <label for="spazi-care" class="spazi-meta-label octolabel">cura</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider five_4 ui" id="spazi-care">
                            <label for="spazi-contemplation" class="spazi-meta-label octolabel">contemplazione</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider six_5 ui" id="spazi-contemplation">
                            <label for="spazi-citizenship" class="spazi-meta-label octolabel">attivismo</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider seven_6 ui" id="spazi-citizenship">
                            <label for="spazi-learning" class="spazi-meta-label octolabel">educazione</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider eight_7 ui" id="spazi-learning">
                            <div class="dot0" id="octomin"></div>
                            <div class="dot1"></div>
                            <div class="dot2"></div>
                            <div class="dot3"></div>
                            <canvas id="octamask" width="400px" height="400px">
<!--
                              place for a backup image
-->
                            </canvas>
                        </div>
                        <figcaption id="octodescription"></figcaption>
                    </figure>
<!--
                    concomitanza di attività
-->
                  <div class="field-select">
                    <label for="spazi-concomitance" class="spazi-meta-label">concomitanza di diverse attività</label>
                    <select class="form-control ui" name="spazi-concomitance" id="spazi-concomitance" onfocusout="onUpdateDatafields(event)">
                        <option value=" "></option>
                        <option value="quotidiane">quotidiana</option>
                        <option value="settimanali">settimanale</option>
                        <option value="saltuarie">saltuaria</option>
                        <option value="annuali">annuale</option>
                        <option value="mai">mai</option>
                    </select>
                  </div>
<!--
                    frequenza attività spontanee
-->
                  <div class="field-select">
                    <label for="spazi-self_organized_activities_frequency" class="spazi-meta-label">ordinarietà delle attività spontanee: </label>
                    <select class="form-control ui" name="spazi-self_organized_activities_frequency" id="spazi-self_organized_activities_frequency" onfocusout="onUpdateDatafields(event)">
                        <option value=" "></option>
                        <option value="quotidiane">quotidiane</option>
                        <option value="settimanali">settimanali</option>
                        <option value="saltuarie">saltuarie</option>
                        <option value="annuali">annuali</option>
                        <option value="mai">mai</option>
                    </select>
                  </div>
<!--
                    frequenza attività organizzate
-->
                  <div class="field-select">
                    <label for="spazi-organized_activities_frequency" class="spazi-meta-label">ordinarietà delle attività organizzate da operatore: </label>
                    <select class="form-control ui" name="spazi-organized_activities_frequency" id="spazi-organized_activities_frequency" onfocusout="onUpdateDatafields(event)">
                        <option value=" "></option>
                        <option value="quotidiane">quotidiane</option>
                        <option value="settimanali">settimanali</option>
                        <option value="saltuarie">saltuarie</option>
                        <option value="annuali">annuali</option>
                        <option value="mai">mai</option>
                    </select>
                  </div>
                  <label for="spazi-participation" class="spazi-meta-label">osservazioni</label>
                  <textarea rows="8" cols="38" class="form-control ui" name="spazi-participation" id="spazi-participation" onfocusout="onUpdateDatafields(event)"></textarea>
                </section>
                <section id="colloqui" class="tab-panel">
                  <h2>Colloqui, discussioni, interviste</h2>
                  <label for="spazi-interviews" class="spazi-meta-label">resoconti</label>
                  <textarea rows="8" cols="38" class="form-control ui" name="spazi-interviews" id="spazi-interviews" onfocusout="onUpdateDatafields(event)"></textarea>
                </section>
                <section id="foto" class="tab-panel">
                  <h2>Fotografie</h2>
                    <div class="spazi-foto form-group">
                        <img id="spazi-picture_1_view" class="form-control" src="" width="80vw" alt="what">
                        <input type="file" class="form-control ui" name="spazi-picture_1" id="spazi-picture_1" accept="image/png, image/jpeg" onchange="onFileSelected(event)">
                    </div>
                    <div class="spazi-foto form-group">
                        <label for="spazi-picture_1_alt" class="spazi-meta-label">descrizione</label>
                        <textarea rows="2" cols="38" class="form-control ui" name="spazi-picture_1_alt" id="spazi-picture_1_alt"></textarea>
                    </div>
                    <hr>
                    <div class="spazi-foto form-group">
                        <img id="spazi-picture_2_view" class="form-control" src="" width="80vw" alt="what">
                        <input type="file" class="form-control ui" name="spazi-picture_2" id="spazi-picture_2" accept="image/png, image/jpeg" onchange="onFileSelected(event)">
                      </div>
                    <div class="spazi-foto form-group">
                        <label for="spazi-picture_2_alt" class="spazi-meta-label">descrizione</label>
                        <textarea rows="2" cols="38" class="form-control ui" name="spazi-picture_2_alt" id="spazi-picture_2_alt"></textarea>
                    </div>
                    <hr>
                    <div class="spazi-foto form-group">
                        <img id="spazi-picture_3_view" class="form-control" src="" width="80vw" alt="what">
                        <input type="file" class="form-control ui" name="spazi-picture_3" id="spazi-picture_3" accept="image/png, image/jpeg" onchange="onFileSelected(event)">
                    </div>
                    <div class="spazi-foto form-group">
                        <label for="spazi-picture_3_alt" class="spazi-meta-label">descrizione</label>
                        <textarea rows="2" cols="38" class="form-control ui" name="spazi-picture_3_alt" id="spazi-picture_3_alt"></textarea>
                    </div>
                </section>
                <section id="meta" class="tab-panel">
                  <h2>Metadati</h2>
                  <div class="rendered-form">
                    <div class="spazi-meta form-group">
                        <label for="spazi-id" class="spazi-meta-label">id del luogo</label>
                        <input type="text" class="form-control" name="spazi-id" id="spazi-id" readonly>
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-datetime_created" class="spazi-meta-label">creazione</label>
                        <input type="text" class="form-control" name="spazi-datetime_created" id="spazi-datetime_created" readonly>
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-created_by" class="spazi-meta-label">autore</label>
                        <input type="text" class="form-control" name="spazi-created_by" id="spazi-created_by" readonly>
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-datetime_last_edited" class="spazi-meta-label">data rev.</label>
                        <input type="text" class="form-control" name="spazi-datetime_last_edited" id="spazi-datetime_last_edited" readonly>
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-version" class="spazi-meta-label">n. revisione</label>
                        <input type="text" class="form-control" name="spazi-version" id="spazi-version" readonly>
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-edited_by" class="spazi-meta-label">revisori</label>
                        <input type="text" class="form-control" name="spazi-edited_by" id="spazi-edited_by" readonly>
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-participatory_process_link" class="spazi-meta-label">URL del processo partecipato</label>
                        <input type="text" class="form-control ui" name="spazi-participatory_process_link" id="spazi-participatory_process_link" onfocusout="onUpdateDatafields(event)">
                    </div>
                    <hr>
                    <h2>Indirizzo</h2>
                    <div class="spazi-meta form-group">
                        <label for="spazi-street" class="spazi-meta-label">indirizzo</label>
                        <input type="text" class="form-control ui" name="spazi-street" id="spazi-street" onfocusout="onUpdateDatafields(event)">
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-housenumber" class="spazi-meta-label">civico</label>
                        <input type="text" class="form-control ui" name="spazi-housenumber" id="spazi-housenumber" onfocusout="onUpdateDatafields(event)">
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-city" class="spazi-meta-label">città</label>
                        <input type="text" class="form-control ui" name="spazi-city" id="spazi-city" onfocusout="onUpdateDatafields(event)">
                    </div>
                    <!-- opening_hours -->
                    <div class="spazi-meta form-group">
                        <label for="opening_hours" class="spazi-meta-label">orario e giorni d'apertura</label><a href="https://wiki.openstreetmap.org/wiki/Key:opening_hours" target="_blank">guida alla formattazione</a>
                        <input type="text" class="form-control ui" name="opening_hours" id="opening_hours" placeholder="Mo-Fr 08:00-12:00,13:00-17:30; Sa 08:00-12:00; PH off" onfocusout="onUpdateDatafields(event)">
                    </div>
                    <hr>
                    <div class="spazi-meta form-group">
                        <p><a id="spazi-linkOSM" href="#" target="_blank">Apri su Openstreetmap</a></p>
                        <p><a id="spazi-linkURI" href="#" target="_blank">Dati in formato JSON</a></p>
                        <p><a id="spazi-linkPP" href="#" target="_blank">Partecipa con la comunità a ridefinire l'uso di questo spazio!</a></p>
                    </div>
                </div>
                </section>
              </div>
            </div>
        </div>
    </section>    
</div>
<div id="cnvUserBox">
    <section class="wrapperUser">
        <div class="profile" id="mpsUser">
            <header id="profileUsername">User</header>
            <table>
            <tbody>
            <tr>
                <td>First name:</td>
                <td id="profileFirst_name"></td>
            </tr>
            <tr>
                <td>Last name:</td>
                <td id="profileLast_name"></td>
            </tr>
            <tr>
                <td>email:</td>
                <td id="profileEmail"></td>
            </tr>
            </tbody>
            </table>
            <div class="user-messages">
                <p>Leggi la <a href="uso.html">guida all'uso</a></p>
            </div>
        <form name="logout" action="#">
            <input type="submit" name="Logout" value="Logout" />
        </form>
        </div>
    </section>    
</div>
<div id="cnvAuthBox">
    <section class="wrapper">
        <div id="mpsAuthentication">
          <div class="form signup">
            <header>Signup</header>
            <form name="register" action="#">
              <input type="text" name="first_name" autocomplete="given-name" placeholder="First name" required />
              <input type="text" name="last_name" autocomplete="family-name" placeholder="Last name" required />
              <input type="text" name="username" autocomplete="username" placeholder="Username" required />
              <input type="email" name="email" autocomplete="email" placeholder="Email address" required />
              <input type="password" name="password" autocomplete="new-password" placeholder="Password" required />
              <div class="checkbox">
                <input type="checkbox" id="signupCheck" name="terms" onclick="checkTerms();" />
                <label for="signupCheck">I accept all <a href="terms-and-conditions.php" target="_blank">terms & conditions</a> and <a href="privacy-policy.php" target="_blank">privacy policy</a></label>
              </div>
              <input type="submit" name="submitregistration" value="Signup" />
            </form>
          </div>
    
          <div class="form login">
            <header>Login</header>
            <form name="access" action="#">
              <input type="text" id="loginUsername" name="username" autocomplete="username" placeholder="Username" required />
              <input type="password" id="loginPassword" name="password" autocomplete="current-password" placeholder="Password" required />
              
              <input type="submit" name="Login" value="Login" />
            </form>
            <div class="loginMessage"><p id="loginMessage"></p></div>
            <div class="forgot"><p><a href="#">Forgot password?</a> sorry, not yet implemented. Register another account.</p></div>
          </div>
    
          <script>
            const wrapper = document.querySelector(".wrapper"),
              signupHeader = document.querySelector(".signup header"),
              loginHeader = document.querySelector(".login header");
    
            loginHeader.addEventListener("click", () => {
              wrapper.classList.add("active");
            });
            signupHeader.addEventListener("click", () => {
              wrapper.classList.remove("active");
            });
          </script>
        </div>
    </section>
</div>
<div id="mpsMap"></div>

</body>
</html>
