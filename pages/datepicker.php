<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Date Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
</head>
    <body>
<input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
<script>
    $(function() {
      // Configuration du DateRangePicker
      $('#daterange').daterangepicker({
        opens: 'left',
        autoUpdateInput: true,
        locale: {
          format: 'MM/DD/YYYY', // Format attendu par votre code PHP
          applyLabel: 'Valider',
          cancelLabel: 'Annuler',
          fromLabel: 'Du',
          toLabel: 'Au',
          customRangeLabel: 'Période personnalisée',
          daysOfWeek: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
          monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
          firstDay: 1
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
      }, function(start, end, label) {
        // Callback pour la sélection de dates
        const tableBody = $('#tableBody');

      });
    });
  </script>
</body>
</html>