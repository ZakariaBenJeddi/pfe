<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../../assets/img/icons/flags/AU.png"><?php //! changer lien   
                                                                                    ?>
    <title>
        Gestion Ecole
    </title>
    <!-- JQuery -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script> -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" /><?php //! changer lien   
                                                                    ?>
    <!-- CSS Files -->
    <link href="../../assets/css/argon-dashboard.css?v=2.0.4" rel="stylesheet" /><?php //! changer lien   
                                                                                    ?>
    <link id="pagestyle" href="../../assets/css/argon-dashboard.css?v=2.0.4" rel="stylsheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/datatable.css">

    <!-- Date Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>


    <!-- //!DRIVER JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css" />

    <!-- //! LINE CHART BLEU YELLOW -->
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.0/apexcharts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap JS  //! dropdown notification -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SWEET alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
            background-color: white;
            height: 100vh;
        }

        .cursor {
            pointer-events: none;
            position: fixed;
            display: block;
            border-radius: 0;
            mix-blend-mode: difference;
            top: 0;
            left: 0;
            z-index: 9999999999999999;
        }

        .circle {
            position: absolute;
            display: block;
            width: 12px;
            height: 12px;
            border-radius: 10px;
            background-color: #fff;
        }

        /* //! START DASHBOARD STYLES */
        /* CSS styles for the button */
        .card {
            position: relative;
            overflow: hidden;
        }

        */

        /* Style the "See More" button */
        .see-more-btn {
            display: none;
            position: absolute;
            bottom: 10px;
            right: 10px;
            padding: 5px 10px;
            background-color: #ffc107;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: bold;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }

        /* Show button on hover */
        .card:hover .see-more-btn {
            display: inline-block;
        }

        /* //! END DASHBOARD STYLES */

        /* //! START SALLE STYLE */
        /* Customize the 'Show entries' select dropdown */
        .dataTables_length {
            margin-left: 15px !important;
        }

        .dataTables_length select {
            margin-left: 13px !important;
            margin-right: 5px !important;
            width: 60px;
            /* Adjust width */
            height: 35px;
            /* Adjust height */
            border: 1px solid #fff;
            border-radius: 10px;
            padding: 5px;
            color: #fff;
            background-color: #5e72e4;
            font-size: 14px;
        }

        /* Customize the search input */
        .dataTables_filter input {
            margin-right: 1.5rem !important;
            width: 200px;
            /* Adjust width */
            height: 35px;
            /* Adjust height */
            border: 1px solid #ccc;
            border-radius: 5px;
            padding-left: 10px;
            color: #333;
            font-size: 14px;
        }

        /* Customize pagination buttons */
        .dataTables_paginate .paginate_button {
            background-color: #007bff;
            /* Set background color */
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            margin: 0 2px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .dataTables_paginate .paginate_button:hover {
            background-color: #0056b3;
            /* Darker color on hover */
        }

        /* Customize active pagination button */
        .dataTables_paginate .paginate_button.current {
            background-color: #0056b3;
            color: #fff;
            font-weight: bold;
        }

        .dataTables_paginate .paginate_button {
            background-color: #5e72e3;
        }


        #table_salle_info {
            margin-left: 15px !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #cfd3db !important;
        }

        /* Remove border between table rows */
        .dataTable tbody tr {
            border-bottom: none;
            border-color: #f4f5f7;
            /* Remove bottom border for each row */
        }

        #table_salle {
            border-bottom: 1px solid #f4f5f7;
        }

        /* display action button */
        #dropdownMenuButton {
            box-shadow: none !important;
        }

        .dropdown .dropdown-menu {
            display: auto !important;
        }

        #changewidth {
            width: 6rem !important;
            min-width: 0 !important;
        }

        /* //! END SALLE STYLE */

        /* //! START ENSAIGNANT TABLE */
        table.dataTable.no-footer {
            border-bottom: 0px solid #fff !important;
        }

        /* Customize the 'Show entries' select dropdown */
        .dataTables_length {
            margin-left: 15px !important;
        }

        .dataTables_length select {
            margin-left: 13px !important;
            margin-right: 5px !important;
            width: 60px;
            /* Adjust width */
            height: 35px;
            /* Adjust height */
            border: 1px solid #fff;
            border-radius: 10px;
            padding: 5px;
            color: #fff;
            background-color: #5e72e4;
            font-size: 14px;
        }

        /* Customize the search input */
        .dataTables_filter input {
            margin-right: 1.5rem !important;
            width: 200px;
            /* Adjust width */
            height: 35px;
            /* Adjust height */
            border: 1px solid #ccc;
            border-radius: 5px;
            padding-left: 10px;
            color: #333;
            font-size: 14px;
        }

        #DataTables_Table_0_info {
            font-size: 12px !important;
        }

        /* Customize pagination buttons */
        .dataTables_paginate .paginate_button {
            background-color: #007bff;
            /* Set background color */
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            margin: 0 2px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .dataTables_paginate .paginate_button:hover {
            background-color: #0056b3;
            /* Darker color on hover */
        }

        /* Customize active pagination button */
        .dataTables_paginate .paginate_button.current {
            background-color: #0056b3;
            color: #fff;
            font-weight: bold;
        }

        .dataTables_paginate .paginate_button {
            background-color: #5e72e3;
        }


        #table_salle_info {
            margin-left: 15px !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #cfd3db !important;
        }

        /* Remove border between table rows */
        .dataTable tbody tr {
            border-bottom: none;
            border-color: #f4f5f7;
            /* Remove bottom border for each row */
        }

        #table_salle {
            border-bottom: 1px solid #f4f5f7;
        }

        /* display action button */
        #dropdownMenuButton {
            box-shadow: none !important;
        }

        .dropdown .dropdown-menu {
            display: auto !important;
        }

        #changewidth {
            width: 6rem !important;
            min-width: 0 !important;
        }

        .icon-container:hover {
            transform: translateY(-10px);
        }

        /* //! END ENSAIGNANT TABLE */
    </style>

</head>