function expo() {
  $('table').each(function() {
    var table = $(this).DataTable();

    // Create data array for headers and rows
    var data = [];
    var headers = [];

    // Extract headers, skipping "Action" column
    table.columns().every(function() {
      if (this.header().textContent !== "Action") {
        headers.push(this.header().textContent.trim());
      }
    });
    data.push(headers);

    // Extract filtered data
    var filteredData = table.rows({
      filter: 'applied'
    }).data();

    filteredData.each(function(valueArray) {
      var rowData = [];
      valueArray.forEach(function(value, index) {
        if (index !== table.columns().count() - 1) {
          rowData.push($('<div>').html(value).text().trim());
        }
      });
      data.push(rowData);
    });
      // Get the current file name without the extension
      var fileName = window.location.pathname.split('/').pop().split('.php')[0];
      // Export to Excel with ExcelJS
      var workbook = new ExcelJS.Workbook();
      var worksheet = workbook.addWorksheet('Data Export');
      data.forEach(function(row) {
        worksheet.addRow(row);
      });
      workbook.xlsx.writeBuffer().then(function(buffer) {
        var blob = new Blob([buffer], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        // Use the file name dynamically
        a.download = fileName + '.xlsx';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
      });
  });
}