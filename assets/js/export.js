function expo() {
  // Obtenir l'instance de DataTable pour la première table
  var table = $('table:first').DataTable();

  // Créer un tableau pour les en-têtes et les lignes
  var data = [];
  var headers = [];

  // Extraire les en-têtes, en sautant la colonne "Action"
  table.columns().every(function() {
    if (this.header().textContent !== "Action") {
      headers.push(this.header().textContent.trim()); // Enlever les espaces en trop
    }
  });
  data.push(headers);

  // Extraire les données filtrées
  var filteredData = table.rows({
    filter: 'applied'
  }).data();

  filteredData.each(function(valueArray) {
    var rowData = [];
    valueArray.forEach(function(value, index) {
      if (index !== 7) { // Sauter la colonne "Action"
        rowData.push($('<div>').html(value).text().trim()); // Extraire le texte propre
      }
    });
    data.push(rowData);
  });

  // Exporter vers Excel avec ExcelJS
  var workbook = new ExcelJS.Workbook();
  var worksheet = workbook.addWorksheet('Data Export');

  // Ajouter les lignes au fichier Excel
  data.forEach(function(row) {
    worksheet.addRow(row);
  });

  // Générer un nom de fichier dynamique
  var fileNameWithExtension = "exported_data.xlsx"; // Par exemple, un nom par défaut

  // Écrire le fichier et le télécharger
  workbook.xlsx.writeBuffer().then(function(buffer) {
    var blob = new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = fileNameWithExtension; // Télécharger avec le nom spécifié
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
  });
}
