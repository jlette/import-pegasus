<h1>Importation et transformation de données CSV</h1>

<p>Veuillez sélectionner le fichier source et la règle de mappage à appliquer.</p>

<form action="index.php?page=process_csv" method="POST" enctype="multipart/form-data">

    <fieldset>
        <legend>Configuration de l'importation</legend>

        <div>
            <label for="csv_file">Fichier source (format CSV) :</label>
            <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
        </div>

        <div>
            <label for="mapping_rule">Règle de transformation :</label>
            <select id="mapping_rule" name="mapping_rule" required>
                <option value="">-- Sélectionnez un schéma --</option>
                <option value="crm_format">Format CRM Standard</option>
                <option value="clean_uppercase">Nettoyage et Majuscules</option>
                <option value="custom_mapping">Mappage personnalisé</option>
            </select>
        </div>

    </fieldset>

    <div>
        <button type="submit">Importer et transformer</button>
    </div>

</form>