<!doctype html>
<html lang="it">
<head>
	<title>Public or vacant spaces map</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta  charset="utf-8">
    <link rel="icon" href="favicon.png">
    <script src="map/alg.js"></script>
    <link rel="stylesheet" type="text/css" href="vendor/leaflet.css">
    <script src="vendor/leaflet.js"></script>
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
    <div class="wrapperPlace">
        <div class="profile" id="mpsPlace">
            <div id="collapse" class="dropdown-content">&#9776;</div>
            <input type="text" class="fout form-control ui" name="spazi-name" id="spazi-name">
            
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
              <input type="radio" name="tabset" id="tab5" aria-controls="colloqui">
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
                  <!-- tipo sottotipo -->
                  <div class="field-select">
                    <label for="spazi-vocation" class="spazi-meta-label">vocazione del luogo: </label>
                    <select class="fout form-control ui" name="spazi-vocation" id="spazi-vocation">
                        <option value="nessuno">nessuna</option>
                        <option value="movimento">movimento</option>
                        <option value="natura">natura</option>
                        <option value="creatività">creatività</option>
                        <option value="comunità">comunità</option>
                        <option value="cura">cura</option>
                        <option value="contemplazione">contemplazione</option>
                        <option value="attivismo">attivismo</option>
                        <option value="educazione">educazione</option>
                    </select>
                  </div>
                  <div class="field-select">
                    <label for="spazi-type" class="spazi-meta-label">tipologia: </label>
                    <select class="fout form-control ui" name="spazi-type" id="spazi-type">
                        <option value="nessuna">nessuna</option>
                        <option value="scuola_k">scuola infanzia</option>
                        <option value="scuola_p">scuola primaria</option>
                        <option value="scuola_m">scuola media</option>
                        <option value="scuola_h">scuola superiore</option>
                        <option value="biblioteca">biblioteca/centro culturale</option>
                        <option value="parrocchia">parrocchia/centro religioso</option>
                        <option value="centro_sportivo">campo sportivo/area gioco</option>
                        <option value="centro_giovani">centro giovani</option>
                        <option value="centro_sociale">circolo/centro sociale</option>
                        <option value="studio">centro studio/doposcuola</option>
                        <option value="centro_volontariato">centro di volontariato</option>
                        <option value="laboratorio_sociale">laboratorio/arti applicate/fablab</option>
                        <option value="centro_anziani">centro anziani</option>
                        <option value="centro_famiglie">centro famiglie</option>
                        <option value="parco">parco/sentiero/prato</option>
                        <option value="piazza">piazza</option>
                        <option value="altro">altro</option>
                    </select>
                  </div>
                  <!-- Rich text editor Pell -->
                  <div id="editor" class="pell"></div>
                  <!-- risorse -->
                  <div class="checkbox">
                      <fieldset id="spazi-resources" class="lbfout">
                      <legend>risorse:</legend>
                        <span><input type="checkbox" class="resources ui" name="locali_interni" id="locali_interni" value="locali_interni">
                        <label for="locali_interni">locali interni</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="spazi_esterni" id="spazi_esterni" value="spazi_esterni">
                        <label for="spazi_esterni">spazi esterni pedonali</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="esterni_illuminati" id="esterni_illuminati" value="esterni_illuminati">
                        <label for="esterni_illuminati">spazi esterni illuminati</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="verde" id="verde" value="verde">
                        <label for="verde">spazi nel verde</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="orti" id="orti" value="orti">
                        <label for="orti">orti</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="calcetto_pingpong" id="calcetto_pingpong" value="calcetto_pingpong">
                        <label for="calcetto_pingpong">calcetto pingpong</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="area_gioco" id="area_gioco" value="area_gioco">
                        <label for="area_gioco">area gioco bimbi</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="campo_sportivo" id="campo_sportivo" value="campo_sportivo">
                        <label for="campo_sportivo">campo sportivo</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="spogliatoi" id="spogliatoi" value="spogliatoi">
                        <label for="spogliatoi">spogliatoi/docce</label></span>
                        
                        <span><input type="checkbox" class="resources ui" name="palestra" id="palestra" value="palestra">
                        <label for="palestra">palestra</label></span>

                        <span><input type="checkbox" class="resources ui" name="servizi" id="servizi" value="servizi">
                        <label for="servizi">servizi igienici</label></span>

                        <span><input type="checkbox" class="resources ui" name="armadietti" id="armadietti" value="armadietti">
                        <label for="armadietti">armadietti/contenitori personali</label></span>

                        <span><input type="checkbox" class="resources ui" name="acqua" id="acqua" value="acqua">
                        <label for="acqua">acqua potabile</label></span>

                        <span><input type="checkbox" class="resources ui" name="fasciatoio" id="fasciatoio" value="fasciatoio">
                        <label for="fasciatoio">fasciatoio</label></span>

                        <span><input type="checkbox" class="resources ui" name="ambulatorio" id="ambulatorio" value="ambulatorio">
                        <label for="ambulatorio">ambulatorio infermieristico</label></span>

                        <span><input type="checkbox" class="resources ui" name="preghiera" id="preghiera" value="preghiera">
                        <label for="preghiera">luogo di preghiera</label></span>

                        <span><input type="checkbox" class="resources ui" name="cucina" id="cucina" value="cucina">
                        <label for="cucina">cucina comunitaria</label></span>

                        <span><input type="checkbox" class="resources ui" name="caffetteria" id="caffetteria" value="caffetteria">
                        <label for="caffetteria">caffetteria</label></span>

                        <span><input type="checkbox" class="resources ui" name="spazio_relax" id="spazio_relax" value="spazio_relax">
                        <label for="spazio_relax">spazio relax</label></span>

                        <span><input type="checkbox" class="resources ui" name="mensa" id="mensa" value="mensa">
                        <label for="mensa">mensa</label></span>

                        <span><input type="checkbox" class="resources ui" name="barbecue" id="barbecue" value="barbecue">
                        <label for="barbecue">cucina esterna/barbecue</label></span>

                        <span><input type="checkbox" class="resources ui" name="sala_polifunzionale" id="sala_polifunzionale" value="sala_feste">
                        <label for="sala_polifunzionale">sala polifunzionale/feste</label></span>

                        <span><input type="checkbox" class="resources ui" name="tavoli_e_sedie" id="tavoli_e_sedie" value="tavoli_e_sedie">
                        <label for="tavoli_e_sedie">tavoli e sedie</label></span>

                        <span><input type="checkbox" class="resources ui" name="WiFi" id="WiFi" value="WiFi">
                        <label for="WiFi">WiFi libero</label></span>

                        <span><input type="checkbox" class="resources ui" name="PC" id="PC" value="PC">
                        <label for="PC">postazioni informatiche</label></span>

                        <span><input type="checkbox" class="resources ui" name="ludoteca" id="ludoteca" value="ludoteca">
                        <label for="ludoteca">ludoteca</label></span>

                        <span><input type="checkbox" class="resources ui" name="oggettoteca" id="oggettoteca" value="oggettoteca">
                        <label for="oggettoteca">oggettoteca</label></span>

                        <span><input type="checkbox" class="resources ui" name="biblioteca" id="biblioteca" value="biblioteca">
                        <label for="biblioteca">biblioteca</label></span>

                        <span><input type="checkbox" class="resources ui" name="quotidiani" id="quotidiani" value="quotidiani">
                        <label for="quotidiani">lettura quotidiani</label></span>

                        <span><input type="checkbox" class="resources ui" name="museo" id="museo" value="museo">
                        <label for="museo">museo</label></span>

                        <span><input type="checkbox" class="resources ui" name="laboratorio" id="laboratorio" value="laboratorio">
                        <label for="laboratorio">laboratorio arti mestieri/fablab</label></span>

                        <span><input type="checkbox" class="resources ui" name="teatro" id="teatro" value="teatro">
                        <label for="teatro">teatro o anfiteatro</label></span>

                        <span><input type="checkbox" class="resources ui" name="proiezioni" id="proiezioni" value="proiezioni">
                        <label for="proiezioni">area per proiezioni</label></span>

                        <span><input type="checkbox" class="resources ui" name="letti" id="letti" value="letti">
                        <label for="letti">posti letto</label></span>

                        <span><input type="checkbox" class="resources ui" name="appartamento" id="appartamento" value="appartamento">
                        <label for="appartamento">appartamento</label></span>
                        
                        <div class="spazi-meta form-group">
                            <label for="spazi-other_resources" class="spazi-meta-label">altro: </label>
                            <input type="text" class="fout form-control ui" name="spazi-other_resources" id="spazi-other_resources">
                        </div>

                      </fieldset>
                  </div>
<!--
                    condizioni delle strutture
-->
                  <div class="field-select">
                    <label for="spazi-status" class="spazi-meta-label">condizioni delle strutture e risorse disponibili</label>
                    <select class="fout form-control ui" name="spazi-status" id="spazi-status">
                        <option value="pessime">pessime</option>
                        <option value="trascurate">trascurate</option>
                        <option value="buone">buone</option>
                        <option value="ottime">ottime</option>
                    </select>
                  </div>
  
              </section>
                <section id="uso" class="tab-panel">
                  <h2>Uso, storia, prospettive</h2>
                  <div class="field-select">
                    <label for="spazi-lifecycle_status" class="spazi-meta-label">stato attuale: </label>
                    <select class="fout form-control ui" name="spazi-lifecycle_status" id="spazi-lifecycle_status">
                        <option value="cantiere">cantiere</option>
                        <option value="utilizzato">utilizzato</option>
                        <option value="inutilizzato">inutilizzato</option>
                        <option value="abbandonato">abbandonato</option>
                    </select>
                  </div>
                  <div class="field-select">
                    <label for="spazi-operator_category" class="spazi-meta-label">gestito da ente: </label>
                    <select class="fout form-control ui" name="spazi-operator_category" id="spazi-operator_category">
                        <option value="privato">privato</option>
                        <option value="pubblico">pubblico</option>
                        <option value="ETS">ETS</option>
                        <option value="coop_società">coop/società</option>
                        <option value="informale">informale</option>
                        <option value="nessuno">nessuno</option>
                    </select>
                  </div>
                  <div class="spazi-meta form-group">
                        <label for="spazi-operator" class="spazi-meta-label">gestore: </label>
                        <input type="text" class="fout form-control ui" name="spazi-operator" id="spazi-operator">
                  </div>
                  <label for="spazi-use" class="spazi-meta-label">osservazioni</label>
                  <textarea rows="8" cols="38" class="fout form-control ui" name="spazi-use" id="spazi-use"></textarea>
                </section>
                <section id="utenza" class="tab-panel">
                  <h2>Bacino d'utenza, accessibilità</h2>
                  <!-- accesso -->
                  <div class="field-select">
                    <label for="spazi-access" class="spazi-meta-label">accesso: </label>
                    <select class="fout form-control ui" name="spazi-access" id="spazi-access">
                        <option value="privato">privato</option>
                        <option value="libero">libero</option>
                        <option value="libero">inaccessibile</option>
                        <option value="oneroso">oneroso</option>
                        <option value="iscritti">iscritti</option>
                        <option value="residenti">residenti</option>
                    </select>
                  </div>
                  <!-- frequentatori --> 
                  <div class="field-select">
                        <label for="spazi-attendees_yearly" class="spazi-meta-label">frequentatori abituali (stima su base annua):
                        <input type="number" min="0" max="30000" value="0" class="fout form-control ui" id="spazi-attendees_yearly"></label>
                  </div>    
                  <div class="field-select">
                        <label for="spazi-attendee_min_age" class="spazi-meta-label">età minima tipica per i destinatari/frequentatori abituali:
                        <input type="number" min="0" max="100" value="0" class="fout form-control ui" id="spazi-attendee_min_age"></label>
                  </div>
                  <div class="field-select">
                        <label for="spazi-attendee_max_age" class="spazi-meta-label">età massima tipica per i destinatari/frequentatori abituali:
                        <input type="number" min="0" max="100" value="0" class="fout form-control ui" id="spazi-attendee_max_age"></label>
                  </div> 
                  <!-- distanze -->  
                  <table class="tg">
                    <thead>
                      <tr>
                        <th class="tg-ul38">Distanze a piedi</th>
                        <th class="tg-ul38">&lt; 5 minuti</th>
                        <th class="tg-ul38">&lt; 10 minuti</th>
                        <th class="tg-ul38">&lt; 15 minuti</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="tg-0lax">densità residenti nell'area raggiungibile [abitanti/ha]</td>
                        <td class="tg-0lax"><input type="number" min="0" max="20000" value="0" class="readonly form-control ui" id="spazi-habha_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="20000" value="0" class="readonly form-control ui" id="spazi-habha_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="20000" value="0" class="readonly form-control ui" id="spazi-habha_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">persone residenti nell'area raggiungibile [ab]</td>
                        <td class="tg-0lax"><input type="number" min="0" max="20000" value="0" class="readonly form-control ui" id="spazi-residents_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="20000" value="0" class="readonly form-control ui" id="spazi-residents_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="20000" value="0" class="readonly form-control ui" id="spazi-residents_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di studenti da uscite scuole materne<br></td>
                        <td class="tg-0lax"><input type="number" min="0" max="2000" value="0" class="readonly form-control ui" id="spazi-students_k_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_k_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_k_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di studenti da uscite scuole elementari</td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_p_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_p_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_p_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di studenti da uscite scuole medie</td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_m_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_m_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_m_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di studenti da uscite scuole superiori</td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_h_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_h_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-students_h_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di persone frequentanti altri poli di aggregazione sociale</td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-social_spaces_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-social_spaces_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" min="0" max="5000" value="0" class="readonly form-control ui" id="spazi-social_spaces_15_m_walk" readonly></td>
                      </tr>
                    </tbody>
                  </table>
 
                  <label for="spazi-accessibility" class="spazi-meta-label">considerazioni</label>
                  <textarea rows="8" cols="38" class="fout form-control ui" name="spazi-accessibility" id="spazi-accessibility"></textarea>
                  <!-- equità -->
                  <div class="checkbox">
                      <fieldset id="spazi-fairness" class="lbfout">
                      <legend>non equità rispetto a...</legend> <!-- disabilità, età, economiche, lingua, orientamento sessuale, dipendenze, etnia, religione, nazionalità o geografica, condizione sociale, convinzione politica -->
                        <span><input type="checkbox" class="fairness ui" id="disabilità" name="disabilità" value="disabilità">
                        <label for="disabilità">disabilità</label></span>

                        <span><input type="checkbox" class="fairness ui" name="età" id="età" value="età">
                        <label for="età">età</label></span>

                        <span><input type="checkbox" class="fairness ui" name="economiche" id="economiche" value="economiche">
                        <label for="lingua">condizioni economiche</label></span>

                        <span><input type="checkbox" class="fairness ui" name="lingua" id="lingua" value="lingua">
                        <label for="lingua">lingua</label></span>

                        <span><input type="checkbox" class="fairness ui" name="sessuale" id="sessuale" value="sessuale">
                        <label for="sessuale">genere o sessuale</label></span>

                        <span><input type="checkbox" class="fairness ui" name="dipendenze" id="dipendenze" value="dipendenze">
                        <label for="dipendenze">dipendenze</label></span>

                        <span><input type="checkbox" class="fairness ui" name="etnia" id="etnia" value="etnia">
                        <label for="etnia">etnia</label></span>

                        <span><input type="checkbox" class="fairness ui" name="religione" id="religione" value="religione">
                        <label for="religione">religione</label></span>

                        <span><input type="checkbox" class="fairness ui" name="geografica" id="geografica" value="geografica">
                        <label for="geografica">origine geografica</label></span>

                        <span><input type="checkbox" class="fairness ui" name="politica" id="politica" value="politica">
                        <label for="politica">idee politiche</label></span>

                      </fieldset>
                  <label for="spazi-fairness_description" class="spazi-meta-label">descrizione delle iniquità</label>
                  <textarea rows="3" cols="38" class="fout form-control ui" name="spazi-fairness_description" id="spazi-fairness_description"></textarea>
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
                            <canvas id="octamask" width="400" height="400">
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
                    <select class="fout form-control ui" name="spazi-concomitance" id="spazi-concomitance">
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
                    <select class="fout form-control ui" name="spazi-self_organized_activities_frequency" id="spazi-self_organized_activities_frequency">
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
                    <select class="fout form-control ui" name="spazi-organized_activities_frequency" id="spazi-organized_activities_frequency">
                        <option value="quotidiane">quotidiane</option>
                        <option value="settimanali">settimanali</option>
                        <option value="saltuarie">saltuarie</option>
                        <option value="annuali">annuali</option>
                        <option value="mai">mai</option>
                    </select>
                  </div>
                  <label for="spazi-participation" class="spazi-meta-label">osservazioni</label>
                  <textarea rows="8" cols="38" class="fout form-control ui" name="spazi-participation" id="spazi-participation"></textarea>
                </section>
                <section id="colloqui" class="tab-panel">
                  <h2>Colloqui, discussioni, interviste</h2>
                  <label for="spazi-interviews" class="spazi-meta-label">resoconti</label>
                  <textarea rows="8" cols="38" class="fout form-control ui" name="spazi-interviews" id="spazi-interviews"></textarea>
                  <div class="spazi-meta form-group">
                        <p><a id="spazi-linkPP" href="#" target="_blank">Partecipa con la comunità per ridefinire l'uso di questo spazio!</a></p>
                  </div>
                </section>
                <section id="foto" class="tab-panel">
                  <h2>Fotografie</h2>
                    <div class="spazi-foto form-group">
                        <img id="spazi-picture_1_view" class="form-control" src="#" width="80" alt="what">
                        <input type="file" class="form-control ui" name="spazi-picture_1" id="spazi-picture_1" accept="image/png, image/jpeg" onchange="onFileSelected(event)">
                    </div>
                    <div class="spazi-foto form-group">
                        <label for="spazi-picture_1_alt" class="spazi-meta-label">descrizione</label>
                        <textarea rows="2" cols="38" class="form-control ui" name="spazi-picture_1_alt" id="spazi-picture_1_alt"></textarea>
                    </div>
                    <hr>
                    <div class="spazi-foto form-group">
                        <img id="spazi-picture_2_view" class="form-control" src="#" width="80" alt="what">
                        <input type="file" class="form-control ui" name="spazi-picture_2" id="spazi-picture_2" accept="image/png, image/jpeg" onchange="onFileSelected(event)">
                      </div>
                    <div class="spazi-foto form-group">
                        <label for="spazi-picture_2_alt" class="spazi-meta-label">descrizione</label>
                        <textarea rows="2" cols="38" class="form-control ui" name="spazi-picture_2_alt" id="spazi-picture_2_alt"></textarea>
                    </div>
                    <hr>
                    <div class="spazi-foto form-group">
                        <img id="spazi-picture_3_view" class="form-control" src="#" width="80" alt="what">
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
                        <label for="spazi-version" class="spazi-meta-label">numero revisione</label>
                        <input type="text" class="form-control" name="spazi-version" id="spazi-version" readonly>
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-edited_by" class="spazi-meta-label">revisori</label>
                        <input type="text" class="form-control" name="spazi-edited_by" id="spazi-edited_by" readonly>
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-participatory_process_link" class="spazi-meta-label">URL del processo partecipato</label>
                        <input type="text" class="fout form-control ui" name="spazi-participatory_process_link" id="spazi-participatory_process_link">
                    </div>
                    <hr>
                    <form name="Delete" action="#">
                        <input type="submit" name="Delete" value="Cancella questa scheda">
                    </form>
                    <hr>
                    <h2>Indirizzo</h2>
                    <div class="spazi-meta form-group">
                        <label for="spazi-street" class="spazi-meta-label">indirizzo</label>
                        <input type="text" class="fout form-control ui" name="spazi-street" id="spazi-street">
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-housenumber" class="spazi-meta-label">civico</label>
                        <input type="text" class="fout form-control ui" name="spazi-housenumber" id="spazi-housenumber">
                    </div>
                    <div class="spazi-meta form-group">
                        <label for="spazi-city" class="spazi-meta-label">città</label>
                        <input type="text" class="fout form-control ui" name="spazi-city" id="spazi-city">
                    </div>
                    <!-- opening_hours -->
                    <div class="spazi-meta form-group">
                        <label for="opening_hours" class="spazi-meta-label">orario e giorni d'apertura</label><a href="https://wiki.openstreetmap.org/wiki/Key:opening_hours" target="_blank">guida alla formattazione</a>
                        <input type="text" class="fout form-control ui" name="opening_hours" id="opening_hours" placeholder="Mo-Fr 08:00-12:00,13:00-17:30; Sa 08:00-12:00; PH off">
                    </div>
                    <hr>
                    <div class="spazi-meta form-group">
                        <p><a id="spazi-linkOSM" href="#" target="_blank">Apri su Openstreetmap</a></p>
                        <p><a id="spazi-linkURI" href="#" target="_blank">Dati in formato JSON</a></p>
                        <p><a id="spazi-linkPDF" href="#" target="_blank">Dati in formato PDF</a></p>
                    </div>
                </div>
                </section>
              </div>
            </div>
        </div>
    </div>    
</div>
<div id="cnvUserBox">
    <section class="wrapperUser">
        <div class="profile" id="mpsUser">
            <h2 id="profileUsername">User</h2>
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
            <input type="submit" name="Logout" value="Logout">
        </form>
        </div>
    </section>    
</div>
<div id="cnvAuthBox">
    <section class="wrapper">
        <div id="mpsAuthentication">
          <div class="form signup">
            <h2>Signup</h2>
            <form name="register" action="#">
              <input type="text" name="first_name" autocomplete="given-name" placeholder="First name" required>
              <input type="text" name="last_name" autocomplete="family-name" placeholder="Last name" required>
              <input type="text" name="username" autocomplete="username" placeholder="Username" required>
              <input type="email" name="email" autocomplete="email" placeholder="Email address" required>
              <input type="password" name="password" autocomplete="new-password" placeholder="Password" required>
              <div class="checkbox">
                <input type="checkbox" id="signupCheck" name="terms">
                <label for="signupCheck">I accept all <a href="terms-and-conditions.php" target="_blank">terms & conditions</a> and <a href="privacy-policy.php" target="_blank">privacy policy</a></label>
              </div>
              <input type="submit" name="submitregistration" value="Signup">
            </form>
          </div>
    
          <div class="form login">
            <h2>Login</h2>
            <form name="access" action="#">
              <input type="text" id="loginUsername" name="username" autocomplete="username" placeholder="Username" required>
              <input type="password" id="loginPassword" name="password" autocomplete="current-password" placeholder="Password" required>
              
              <input type="submit" name="Login" value="Login">
            </form>
            <div class="loginMessage"><p id="loginMessage"></p></div>
            <div class="forgot"><p><a href="#">Forgot password?</a> sorry, not yet implemented. Register another account.</p></div>
          </div>
        </div>
    </section>
</div>
<div id="mpsMap"></div>

</body>
</html>
