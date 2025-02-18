<!doctype html>
<html lang="it">
<head>
	<title>Public or vacant spaces map</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta  charset="utf-8">
<?php
// Check if markerID is present in the request
if (!isset($_GET["markerID"])) {
    // Default values for the meta tags
    $defaultTitle = "Mappatura collaborativa di Alba";
    $defaultDescription = "realizziamo assieme una mappa degli spazi sociali";
    $defaultUrl = "https://collab.42web.io";
    $defaultImage = "https://collab.42web.io/logo.jpg";

    // Echo the default meta tags
    echo '<meta property="og:title" content="' .
        $defaultTitle .
        '" />' .
        PHP_EOL;
    echo '<meta property="og:description" content="' .
        $defaultDescription .
        '" />' .
        PHP_EOL;
    echo '<meta property="og:url" content="' . $defaultUrl . '" />' . PHP_EOL;
    echo '<meta property="og:image" content="' .
        $defaultImage .
        '" />' .
        PHP_EOL;
} else {
    // Open SQLite database
    $db = new SQLite3("../database/spaces.db");

    // Get the markerID from the URL
    $markerID = (int) $_GET["markerID"];

    // Prepare and execute the query to fetch data based on markerID
    $query = $db->prepare(
        "SELECT id,name,description,picture_1 FROM spaces WHERE id = :markerID"
    );
    $query->bindValue(":markerID", $markerID, SQLITE3_INTEGER);
    $result = $query->execute();

    // Fetch the row
    $row = $result->fetchArray(SQLITE3_ASSOC);

    $allowed = ["jpg", "png"];
    $default_image = "images/default.jpg";
    $ext = strtolower(pathinfo($row["picture_1"], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {
        $miniature_image_path =
            "images/places/miniatures/" . basename($row["picture_1"]);
    } else {
        $miniature_image_path = $default_image;
    }

    if (!file_exists($miniature_image_path)) {
        $path_parts = pathinfo($row["picture_1"]);
        // Create the miniature image
        if ($path_parts["extension"] == "jpg") {
            $original_image = imagecreatefromjpeg($row["picture_1"]);
            $miniature_image = imagecreatetruecolor(256, 256);
            $side = min(imagesx($original_image), imagesy($original_image));
            imagecopyresampled(
                $miniature_image,
                $original_image,
                0,
                0,
                0,
                0,
                256,
                256,
                $side,
                $side
            );
            imagejpeg($miniature_image, $miniature_image_path, 90);
        } elseif ($path_parts["extension"] == "png") {
            $miniature_image = imagecreatetruecolor(256, 256);
            $original_image = imagecreatefrompng($row["picture_1"]);
            $side = min(imagesx($original_image), imagesy($original_image));
            imagecopyresampled(
                $miniature_image,
                $original_image,
                0,
                0,
                0,
                0,
                $new_width,
                $new_height,
                $side,
                $side
            );
            imagepng($miniature_image, $miniature_image_path);
        }
        // Free up memory
        imagedestroy($original_image);
        imagedestroy($miniature_image);
    }

    // Fill the HTML tags with the retrieved data
    echo '<meta property="og:title" content="' .
        htmlspecialchars(substr($row["name"], 0, 55), ENT_QUOTES) .
        '" />' .
        PHP_EOL;
    echo '<meta property="og:description" content="' .
        htmlspecialchars(
            substr(strip_tags($row["description"]), 0, 80),
            ENT_QUOTES
        ) .
        '..." />' .
        PHP_EOL;

    $clean_id = (int) $row["id"];
    $image_url = file_exists($miniature_image_path)
        ? rawurlencode($miniature_image_path)
        : "images/places/default.jpg";

    echo '<meta property="og:url" content="https://collab.42web.io/?markerID=' .
        $clean_id .
        '" />' .
        PHP_EOL;
    echo '<meta property="og:image" content="https://collab.42web.io/' .
        htmlspecialchars($image_url, ENT_QUOTES) .
        '" />' .
        PHP_EOL;

    // Close the database connection
    $db->close();
} ?>
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
    <link rel="stylesheet" type="text/css" href="map/tooltip.css">
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
                    <label for="spazi-vocation" class="spazi-meta-label tooltip">vocazione del luogo:<span class="tttext">Qual'è la principale ragione d'essere dello spazio, nel caso sia organizzato e ospiti attività.
                    <br>Nel caso non ci siano attività può comunque essere dotato di strutture tali da favorire una certa vocazione.
                    <br>Se non è così usare la voce 'nessuna'. <b>Il colore del marcatore sulla mappa cambierà in base a questa selezione.</b>
                    <br><b>Movimento:</b> per esempio camminare, fare sport, ballare.<br><b>Natura:</b> osservare o interagire con la natura e prendersi cura dell'ambiente.
                    <br><b>Pensiero critico e creatività:</b> La crescita di individui come soggetti autonomi e capaci di esprimere il loro pensiero critico. Per esempio confrontandosi su vari temi, la creazione o l'espressione artistica.
                    <br><b>Socializzazione:</b> L'integrazione degli individui nella società e nella cultura.
                    <br><b>Cura:</b> L'espressione degli individui come soggetti capaci di cura.
                    <br><b>Introspezione:</b> Processi di crescita personale basati sulla consapevolezza di sé, del proprio valore, delle proprie risorse.
                    <br><b>Attivismo:</b> attività civiche, politiche, culturali o sociali che generano un valore o che promuovono il benessere e lo sviluppo delle comunità.
                    <br><b>Formazione e memoria:</b> La trasmissione di conoscenze, abilità e della memoria collettiva. Per esempio lo studio in gruppo e la partecipazione ad attività educative.</span></label>
                    <select class="fout form-control ui" name="spazi-vocation" id="spazi-vocation">
                        <option value="nessuna">nessuna</option>
                        <option value="movimento">movimento</option>
                        <option value="natura">natura</option>
                        <option value="creatività">pensiero critico e creatività</option>
                        <option value="socializzazione">socializzazione</option>
                        <option value="cura">cura</option>
                        <option value="introspezione">introspezione</option>
                        <option value="attivismo">attivismo</option>
                        <option value="formazione">formazione e memoria</option>
                    </select>
                  </div>
                  <div class="field-select">
                    <label for="spazi-type" class="spazi-meta-label tooltip">tipologia: <span class="tttext">Lo spazio rientra in una delle tipologie elencate? Il dato è utile per i filtri di ricerca.</span></label>
                    <select class="fout form-control ui" name="spazi-type" id="spazi-type">
                        <option value="nessuna">nessuna</option>
                        <option value="scuola_k">scuola infanzia</option>
                        <option value="scuola_p">scuola primaria</option>
                        <option value="scuola_m">scuola media</option>
                        <option value="scuola_h">scuola superiore</option>
                        <option value="biblioteca">biblioteca/libreria/centro culturale</option>
                        <option value="parrocchia">parrocchia/centro religioso</option>
                        <option value="cinema_teatro">cinema/teatro</option>
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
                      <legend class="tooltip">risorse:<span class="tttext">Risorse presenti, anche se non utilizzate/utilizzabili dai frequentatori</span></legend>
                        <span><input type="checkbox" class="resources ui" name="locali_interni" id="locali_interni" value="locali_interni">
                        <label for="locali_interni">locali interni</label></span>

                        <span><input type="checkbox" class="resources ui" name="spazi_esterni" id="spazi_esterni" value="spazi_esterni">
                        <label for="spazi_esterni" class="tooltip">spazi esterni pedonali<span class="tttext">intesi come spazi esenti da traffico veicolare</span></label></span>

                        <span><input type="checkbox" class="resources ui" name="esterni_illuminati" id="esterni_illuminati" value="esterni_illuminati">
                        <label for="esterni_illuminati">spazi esterni illuminati</label></span>

                        <span><input type="checkbox" class="resources ui" name="esterni_coperti" id="esterni_coperti" value="esterni_coperti">
                        <label for="esterni_coperti">spazi esterni coperti</label></span>

                        <span><input type="checkbox" class="resources ui" name="soglia_impalpabile" id="soglia_impalpabile" value="soglia_impalpabile">
                        <label for="soglia_impalpabile" class="tooltip">soglia "debole"<span class="tttext">Sono presenti condizioni architettoniche per le quali è in qualche misura impercettibile il passaggio tra dentro e fuori, condizione che abbassa le resistenze all'entrare in esso (esempio: mercato, parco pubblico privo di recinzioni).</span></label></span>

                        <span><input type="checkbox" class="resources ui" name="sedute_comunitarie" id="sedute_comunitarie" value="sedute_comunitarie">
                        <label for="sedute_comunitarie" class="tooltip">spazi per sedersi socializzanti<span class="tttext">Per esempio tavolate uniche o gradinate dove potenziali sconosciuti possono trovarsi vicino.</span></label></span>

                        <span><input type="checkbox" class="resources ui" name="verde" id="verde" value="verde">
                        <label for="verde">spazi nel verde</label></span>

                        <span><input type="checkbox" class="resources ui" name="orti" id="orti" value="orti">
                        <label for="orti">orti</label></span>

                        <span><input type="checkbox" class="resources ui" name="calcetto_pingpong" id="calcetto_pingpong" value="calcetto_pingpong">
                        <label for="calcetto_pingpong">calciobalilla pingpong</label></span>

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
                        <label for="WiFi">WiFi</label></span>

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
                    <label for="spazi-status" class="spazi-meta-label tooltip">condizioni delle strutture e risorse disponibili<span class="tttext"><b>pessime</b>: inutilizzabili, in rovina o con gravi non conformità di sicurezza. <b>Trascurate</b>: bisognose di molta manutenzione e rinnovi. <b>Buone</b>: necessitano di pochi interventi di manutenzione. <b>Ottime</b>: strutture in perfetto stato.</span></label>
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
                        <label for="spazi-operator" class="spazi-meta-label tooltip">gestore: <span class="tttext">Indicare il nome o ragione sociale.</span></label>
                        <input type="text" class="fout form-control ui" name="spazi-operator" id="spazi-operator">
                  </div>
                  <div class="spazi-meta form-group">
                        <label for="spazi-contacts" class="spazi-meta-label tooltip"><span class="tttext">Indicare, previo consenso, nome ed eventualmente contatti della persona referente.</span>referente e contatti: </label>
                        <input type="text" class="fout form-control ui" name="spazi-contacts" id="spazi-contacts">
                  </div>
                  <label for="spazi-use" class="spazi-meta-label tooltip"><span class="tttext">Qual'è la storia di questo spazio? Ci sono progetti, o proposte da portatori di interesse?</span>osservazioni</label>
                  <textarea rows="8" cols="38" class="fout form-control ui" name="spazi-use" id="spazi-use"></textarea>
                </section>
                <section id="utenza" class="tab-panel">
                  <h2>Bacino d'utenza, accessibilità</h2>
                  <!-- accesso -->
                  <div class="field-select">
                    <label for="spazi-access" class="spazi-meta-label tooltip">accesso: <span class="tttext"><b>Libero</b>: è possibile entrare e circolare liberamente in tutto o gran parte dello spazio. <br><b>Parziale</b>: è possibile entrare e circolare liberamente solo in una parte dello spazio (escludendo locali tecnici e di servizio). <br><b>Inaccessibile</b>: Non è possibile accedere.</span></label>
                    <select class="fout form-control ui" name="spazi-access" id="spazi-access">
                        <option value="libero">libero</option>
                        <option value="parziale">parziale</option>
                        <option value="inaccessibile">inaccessibile</option>
                    </select>
                  </div>
                  <div class="field-select">
                    <label for="spazi-access_title" class="spazi-meta-label tooltip">titolarità d'accesso: <span class="tttext"><b>Privato</b>: accesso riservato a proprietari/locatari. <br><b>Pubblico</b>: É possibile accedere senza avere requisiti specifici. <br><b>Oneroso</b>: accesso condizionato da un pagamento per ottenere un servizio o riservare uno spazio, nel caso si applichi anche 'iscritti' scegliere comunque questo. <br><b>Iscritti</b>: accesso ristretto a chi è iscritto. <br><b>Residenti</b>: struttura esplicitamente riservata ai residenti (del complesso, quartiere, condominio, ...)</span></label>
                    <select class="fout form-control ui" name="spazi-access_title" id="spazi-access_title">
                        <option value="privato">privato</option>
                        <option value="pubblico">pubblico</option>
                        <option value="oneroso">oneroso</option>
                        <option value="iscritti">iscritti</option>
                        <option value="residenti">residenti</option>
                    </select>
                  </div>
                  <label for="spazi-accessibility" class="spazi-meta-label">considerazioni</label>
                  <textarea rows="8" cols="38" class="fout form-control ui" name="spazi-accessibility" id="spazi-accessibility"></textarea>
                  <!-- frequentatori -->
                  <div class="field-select">
                        <label for="spazi-attendees_yearly" class="spazi-meta-label tooltip">frequentatori abituali giornalieri: <span class="tttext">Quanti frequentatori accedono allo spazio, in un giorno in cui è aperto? Non considerare picchi eccezionali di affluenza: determinare un valore medio su base annua per i giorni di apertura. Nel caso non si sappia, lasciare vuoto</span></label>
                        <input type="number" min="-1" max="30000" value="0" class="fout form-control ui" id="spazi-attendees_yearly">
                  </div>
                  <div class="field-select">
                        <label for="spazi-attendee_min_age" class="spazi-meta-label tooltip">età minima per i frequentatori: <span class="tttext">Esistono condizioni che restringono l'età minima tipica di chi frequenta lo spazio? Nel caso non ci sia età minima lasciare vuoto</span></label>
                        <input type="number" min="-1" value="0" class="fout form-control ui" id="spazi-attendee_min_age">
                  </div>
                  <div class="field-select">
                        <label for="spazi-attendee_max_age" class="spazi-meta-label tooltip">età massima per i frequentatori: <span class="tttext">Esistono condizioni che restringono l'età massima tipica di chi frequenta lo spazio? Nel caso non ci sia età massima lasciare vuoto</span></label>
                        <input type="number" min="-1" max="100" value="0" class="fout form-control ui" id="spazi-attendee_max_age">
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
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-habha_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-habha_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-habha_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">persone residenti nell'area raggiungibile [ab]</td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-residents_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-residents_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-residents_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di studenti da uscite scuole materne<br></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_k_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_k_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_k_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di studenti da uscite scuole elementari</td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_p_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_p_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_p_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di studenti da uscite scuole medie</td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_m_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_m_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_m_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di studenti da uscite scuole superiori</td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_h_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_h_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-students_h_15_m_walk" readonly></td>
                      </tr>
                      <tr>
                        <td class="tg-0lax">n. di persone frequentanti altri poli di aggregazione sociale</td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-social_spaces_5_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-social_spaces_10_m_walk" readonly></td>
                        <td class="tg-0lax"><input type="number" class="readonly form-control ui" id="spazi-social_spaces_15_m_walk" readonly></td>
                      </tr>
                    </tbody>
                  </table>
                  <!-- equità -->
                  <div class="checkbox">
                      <fieldset id="spazi-fairness" class="lbfout">
                      <legend>Organizzazione, pratiche e strutture attente e aperte a...</legend> <!-- disabilità, età, economiche, lingua, orientamento sessuale, dipendenze, etnia, religione, nazionalità o geografica, condizione sociale, convinzione politica -->
                        <span><input type="checkbox" class="fairness ui" id="disabilità" name="disabilità" value="disabilità">
                        <label for="disabilità">disabilità</label></span>

                        <span><input type="checkbox" class="fairness ui" name="infanzia" id="infanzia" value="infanzia">
                        <label for="infanzia">prima infanzia e maternità</label></span>

                        <span><input type="checkbox" class="fairness ui" name="fragilità" id="fragilità" value="fragilità">
                        <label for="fragilità">fragilità</label></span>

                        <span><input type="checkbox" class="fairness ui" name="terza_età" id="terza_età" value="terza_età">
                        <label for="terza_età">terza e quarta età</label></span>

                        <span><input type="checkbox" class="fairness ui" name="economiche" id="economiche" value="economiche">
                        <label for="lingua">condizioni economiche difficili</label></span>

                        <span><input type="checkbox" class="fairness ui" name="lingua" id="lingua" value="lingua">
                        <label for="lingua">lingua</label></span>

                        <span><input type="checkbox" class="fairness ui" name="sessuale" id="sessuale" value="sessuale">
                        <label for="sessuale">diversità di genere, LGBT+ e sessuale</label></span>

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
                  <label for="spazi-fairness_description" class="spazi-meta-label">descrizione delle pratiche</label>
                  <textarea rows="8" cols="38" class="fout form-control ui" name="spazi-fairness_description" id="spazi-fairness_description"></textarea>
                  </div>
                </section>
                <section id="partecipazione" class="tab-panel">
                  <h2>Opportunità di partecipazione</h2>
                    <div class="user-messages">
                      <p class="justify">Raccogli dati con il <a href="https://forms.gle/rpuz6DiLjb21oKy47">questionario</a>. Invita chi frequenta lo spazio a compilarlo. Inseriremo nel diagramma qui sotto e nei campi pertinenti i dati raccolti (in forma anonima e aggregata).</p>
                    </div>
                  <div class="field-select">
                        <label for="spazi-collected_surveys" class="spazi-meta-label">Numero di questionari raccolti:
                        <input type="number" min="0" value="0" class="fout form-control ui" id="spazi-collected_surveys"></label>
                  </div>
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
                            <label for="spazi-introspection" class="spazi-meta-label octolabel">introspezione</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider six_5 ui" id="spazi-introspection">
                            <label for="spazi-citizenship" class="spazi-meta-label octolabel">attivismo</label>
                            <input type="range" min="0" max="3" value="0" class="octoslider seven_6 ui" id="spazi-citizenship">
                            <label for="spazi-learning" class="spazi-meta-label octolabel">formazione</label>
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
                    <label for="spazi-concomitance" class="spazi-meta-label tooltip">possibilità di incontri fortuiti<span class="tttext">Dato utile a comprendere la possibilità di incontri non programmati. Esprime le potenzialità che ha lo spazio di favorire nuove relazioni: la concomitanza di attività in 'celle stagne' prive di luoghi o tempi di contatto non favorisce questa possibilità.</span></label>
                    <select class="fout form-control ui" name="spazi-concomitance" id="spazi-concomitance">
                        <option value="quotidiane">quotidiana</option>
                        <option value="settimanali">settimanale</option>
                        <option value="saltuarie">saltuaria</option>
                        <option value="annuali">annuale</option>
                        <option value="mai">mai</option>
                    </select>
                  </div>
<!--
                    architettura e disegno degli spazi a favore della socialità
-->
<!--
                  <div class="field-select">
                    <label for="spazi-architecture" class="spazi-meta-label tooltip">spazi che favoriscono la socialità<span class="tttext">Dato utile a comprendere quanto lo spazio fisico favorisca o pregiudichi l'incontro. Oppure lo spazio è vuoto e non ha elementi a favore o contro la possibilità di ritrovarsi? Quale tipo di socialità: fugace o durevole? La presenza di sedute. L'uso di lunghe sedute uniche, panchine vicine e tavoli unici al posto di sedute singole, tavolini per piccoli gruppi.</span></label>
                    <select class="fout form-control ui" name="spazi-architecture" id="spazi-architecture">
                        <option value="quotidiane">quotidiana</option>
                        <option value="settimanali">settimanale</option>
                        <option value="saltuarie">saltuaria</option>
                        <option value="annuali">annuale</option>
                        <option value="mai">mai</option>
                    </select>
                  </div>
-->
<!--
                    frequenza attività spontanee
-->
                  <div class="field-select">
                    <label for="spazi-self_organized_activities_frequency" class="spazi-meta-label tooltip">ordinarietà delle attività spontanee: <span class="tttext">Si considerino attività organizzate dai <b>frequentatori</b> dello spazio</span></label>
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
                    <label for="spazi-organized_activities_frequency" class="spazi-meta-label tooltip">ordinarietà delle attività istituzionali: <span class="tttext">Si considerino attività organizzate dagli <b>operatori</b> dello spazio</span></label>
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

                  <!--
                    partecipazione nella proprietà
                  -->
                  <div class="field-select">
                    <label for="spazi-participation_property" class="spazi-meta-label">partecipazione nella proprietà dei beni</label>
                    <select class="fout form-control ui" name="spazi-participation_property" id="spazi-participation_property">
                        <option value="esclusi">esclusi</option>
                        <option value="informati">informati</option>
                        <option value="consultati">consultati</option>
                        <option value="inclusi">inclusi</option>
                        <option value="partecipi">partecipi</option>
                    </select>
                  </div>
                  <!--
                    partecipazione nella direzione
                  -->
                  <div class="field-select">
                    <label for="spazi-participation_direction" class="spazi-meta-label">partecipazione nella direzione</label>
                    <select class="fout form-control ui" name="spazi-participation_direction" id="spazi-participation_direction">
                        <option value="esclusi">esclusi</option>
                        <option value="informati">informati</option>
                        <option value="consultati">consultati</option>
                        <option value="inclusi">inclusi</option>
                        <option value="partecipi">partecipi</option>
                    </select>
                  </div>
                  <!--
                    partecipazione nella pianificazione
                  -->
                  <div class="field-select">
                    <label for="spazi-participation_planning" class="spazi-meta-label">partecipazione nella pianificazione</label>
                    <select class="fout form-control ui" name="spazi-participation_planning" id="spazi-participation_planning">
                        <option value="esclusi">esclusi</option>
                        <option value="informati">informati</option>
                        <option value="consultati">consultati</option>
                        <option value="inclusi">inclusi</option>
                        <option value="partecipi">partecipi</option>
                    </select>
                  </div>
                  <!--
                    partecipazione nella attività
                  -->
                  <div class="field-select">
                    <label for="spazi-participation_labor" class="spazi-meta-label">partecipazione nelle attività</label>
                    <select class="fout form-control ui" name="spazi-participation_labor" id="spazi-participation_labor">
                        <option value="esclusi">esclusi</option>
                        <option value="informati">informati</option>
                        <option value="consultati">consultati</option>
                        <option value="inclusi">inclusi</option>
                        <option value="partecipi">partecipi</option>
                    </select>
                  </div>
                  <!--
                    partecipazione nella comunicazione
                  -->
                  <div class="field-select">
                    <label for="spazi-participation_communication" class="spazi-meta-label">partecipazione nella comunicazione</label>
                    <select class="fout form-control ui" name="spazi-participation_communication" id="spazi-participation_communication">
                        <option value="esclusi">esclusi</option>
                        <option value="informati">informati</option>
                        <option value="consultati">consultati</option>
                        <option value="inclusi">inclusi</option>
                        <option value="partecipi">partecipi</option>
                    </select>
                  </div>
                </section>
                <section id="colloqui" class="tab-panel">
                  <h2>Colloqui, discussioni, interviste</h2>
                  <label for="spazi-interviews" class="spazi-meta-label">resoconti</label>
                  <textarea rows="8" cols="38" class="fout form-control ui" name="spazi-interviews" id="spazi-interviews"></textarea>
                  <div class="spazi-meta form-group">
                        <p><a id="spazi-linkPP" href="#" target="_blank">Partecipa con la comunità per ridefinire l'uso di questo spazio!</a></p>
                  </div>

                  <h3>Discussione</h3>
                  <p>In merito a schedatura, questionari, interviste su questo spazio.</p>
                  <form name="posta">
                  <label for="forum-post" class="spazi-meta-label">scrivi e rispondi</label>
                  <textarea rows="8" cols="38" class="form-control ui" name="forumPost" id="forum-post"></textarea>
                  <input type="submit" name="Posta" value="invia">
                  </form>
                  <div name="talk" id="talk"></div>
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
                                      <!-- stage della scheda -->
                  <div class="field-select stage">
                    <label for="spazi-stage" class="spazi-meta-label tooltip">stato della scheda: <span class="tttext"><b>Da completare</b>: chiunque può proseguire la compilazione. <b>In lavorazione</b>: qualcuno sta lavorando su questa scheda, si prega di non interferire, salvo aggiungere commenti nel pannello 'discussioni'. <b>Completa</b>: tutti i campi che aveva senso compilare sono stati compilati. Eventualmente verificare la data dell'ultima modifica, se è superiore ad un anno si può verificare che i dati siano ancora validi.</span></label>
                    <select class="fout form-control ui" name="spazi-stage" id="spazi-stage">
                        <option value="da_completare">da completare</option>
                        <option value="in_lavorazione">in lavorazione</option>
                        <option value="completa">completa</option>

                    </select>
                  </div>
                    <br>
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
                    <div class="spazi-meta form-group">
                        <h2>Condividi</h2>
                        <p>Tocca qui per copiare il <a id="spazi-condividiLink" href="#" target="_blank">Link a questa scheda &#128203;</a></p>
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
                <td>Nome:</td>
                <td id="profileFirst_name"></td>
            </tr>
            <tr>
                <td>Cognome:</td>
                <td id="profileLast_name"></td>
            </tr>
            <tr>
                <td>email:</td>
                <td id="profileEmail"></td>
            </tr>
            </tbody>
            </table>
            <div class="user-messages">
                <p>Guarda la <a href="uso.html">guida all'uso</a></p>
            </div>

        <form name="DelUser" class="deluser" action="#">
          <input type="submit" name="DeleteUser" id="DeleteUser" value="Elimina l'utente">
        </form>

        <form name="logout" action="#">
          <input type="submit" name="Logout" value="Logout">
        </form>
        </div>
    </section>
</div>
<!-- entrances -->
<div id="cnvEntrancesBox">
        <div class="profile" id="mpsEntrances">
          <form name="entrances" action="#">
            <div>
            <label for="entrances-city" class="entrances-meta-label">Città</label></td>
            <input type="text" class="form-control ui" name="entrances-city" id="entrances-city">
            </div>
            <div>
            <label for="entrances-street" class="entrances-meta-label">Strada/via</label></td>
            <input type="text" class="form-control ui" name="entrances-street" id="entrances-street">
            </div>
            <div>
            <label for="entrances-street_number" class="entrances-meta-label">Numero civico</label></td>
            <input type="text" class="form-control ui" name="entrances-street_number" id="entrances-street_number">
            </div>
            <div>
            <label for="entrances-flats_count" class="entrances-meta-label">Interni uso abitazione</label></td>
            <input type="number" class="form-control spinner ui" name="entrances-flats_count" id="entrances-flats_count">
            </div>
            <div>
            <label for="entrances-inhabited_flats_count" class="entrances-meta-label">Interni abitati (&#128236;)</label></td>
            <input type="number" class="form-control spinner ui" name="entrances-inhabited_flats_count" id="entrances-inhabited_flats_count">
            </div>
            <div>
            <input type="hidden" name="entrances-id" id="entrances-id" value="">
            </div>
            <div class="clear">
              <input type="button" name="cancelEntrances" value="Annulla" />
              <input type="submit" name="Entrances" value="Invia">
            </div>
          </form>
        </div>
</div>
<div id="popupWelcomeBox" class="popup">
    <span class="close" onclick="closePopup()">&times;</span>
    <p>Siamo sulla mappa sociale collaborativa di Alba, dove raccogliamo informazioni sugli spazi albesi che hanno o potrebbero avere importanza per le comunità.</p>
    <p>Puoi <a href="iscrizione.html">iscriverti</a> ed <a href="uso.html">inserire schede</a> degli spazi aiutandoti con le guide o chiedendo aiuto via <a href="mailto:alba@collab.42web.io?subject=richiesta%20supporto%20per%20collab.42web.io">email</a>.</p>
    <p>Sentiti liberə di curiosare, contribuire, discutere: questo dovrebbe essere uno spazio di comunità, seppure virtuale.</p>
    <p>Sei responsabile per quello che fai, per cui controlla i dati che inserisci e verifica con chi frequenta e opera nello spazio la loro correttezza. Inoltre è buona norma chiedere il loro consenso prima di pubblicare le informazioni che li riguardano.</p>
    <p>Questa mappatura non è completa, è necessario il contributo di tutti! Nel corso della primavera 2024 aggiungeremo interessanti modalità di visualizzazione dei dati raccolti, direttamente sulla mappa.</p>
    <label for="noShow"><input type="checkbox" id="noShow">Non mostrare più questo riquadro.</label>
</div>
<div id="cnvAuthBox">
    <section class="wrapper">
        <div id="mpsAuthentication">
          <div class="form signup">
            <h2>Signup</h2>
            <div class="user-messages">
              <p>Guarda la <a href="uso.html">guida all'uso</a></p>
              <p><a href="mailto:alba@collab.42web.io?subject=segnalazione%20problema%20del%20sito%20collab.42web.io&body=Mentre%20usavo%20il%20sito%20ho%20riscontrato%20questo%20problema%3A%0A%0A%C3%A8%20successo%20mentre%20stavo...%0A%0Asto%20navigando%20da%3A%20telefono%2Fcomputer%0A%0Acon%20il%20browser%3A%20%0A">Segnala</a> un problema.</p>
            </div>
            <form name="register" action="#">
              <input class="registr" type="text" name="first_name" autocomplete="given-name" placeholder="First name" required>
              <input class="registr" type="text" name="last_name" autocomplete="family-name" placeholder="Last name" required>
              <input class="registr" type="text" name="username" autocomplete="username" placeholder="Username" required>
              <input class="registr" type="email" name="email" autocomplete="email" placeholder="Email address" required>
              <input class="registr" type="password" name="password" autocomplete="new-password" placeholder="Password" required>
              <div class="checkbox">
                <input type="checkbox" id="signupCheck" name="terms">
                <label for="signupCheck">Accetto i <a href="terms-and-conditions.php" target="_blank">termini e condizioni</a> e le <a href="privacy-policy.php" target="_blank">politiche sulla privacy</a></label>
              </div>
              <input type="submit" name="submitregistration" value="Signup">
            </form>
            <div class="registerMessage"><p id="registerMessage"></p></div>
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
