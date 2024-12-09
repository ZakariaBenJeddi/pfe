
<?php
  // Intégré dans le fichier HTML comme backend PHP
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Connexion à la base de données
    $host = 'localhost';
    $db = 'pfe1';
    $user = 'root';
    $password = '';

    try {
      $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Requête pour récupérer les élèves
      $query = $pdo->query("SELECT id_eleve, prenom FROM eleves");
      $eleves = $query->fetchAll(PDO::FETCH_ASSOC);

      // Renvoie des données au format JSON
      echo json_encode($eleves);
    } catch (PDOException $e) {
      echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Combobox avec Options Dynamiques</title>
  <style>
    .popover { position: relative; display: inline-block; }
    .popover-content { display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid #ddd; width: 200px; z-index: 1000; }
    .popover.open .popover-content { display: block; }
    .button { padding: 8px 12px; width: 200px; cursor: pointer; }
    .list-item { padding: 8px 12px; cursor: pointer; }
    .list-item:hover { background-color: #f0f0f0; }
    .list-item.selected { font-weight: bold; }
  </style>
</head>
<body>
  <div class="popover">
    <button class="button" id="popoverTrigger">Select student...</button>
    <div class="popover-content">
      <input
        type="text"
        id="searchInput"
        placeholder="Search student..."
        style="width: 100%; padding: 8px; box-sizing: border-box;"
      />
      <div id="list">
        <!-- Options dynamiques -->
      </div>
    </div>
  </div>

  <script>
    const popover = document.querySelector(".popover");
    const trigger = document.getElementById("popoverTrigger");
    const list = document.getElementById("list");
    const searchInput = document.getElementById("searchInput");

    let students = [];
    let selectedValue = "";

    // Récupération des élèves depuis le serveur PHP intégré
    async function fetchStudents() {
      try {
        const response = await fetch(window.location.href, { method: "POST" });
        const data = await response.json();
        if (data.error) {
          console.error(data.error);
        } else {
          students = data;
          renderList();
        }
      } catch (error) {
        console.error("Erreur lors de la récupération des élèves :", error);
      }
    }

    // Rendre la liste des élèves
    function renderList(filter = "") {
      list.innerHTML = "";

      const filteredStudents = students.filter((student) =>
        student.prenom.toLowerCase().includes(filter.toLowerCase())
      );

      if (filteredStudents.length === 0) {
        list.innerHTML = '<div class="list-item">No student found.</div>';
        return;
      }

      filteredStudents.forEach((student) => {
        const item = document.createElement("div");
        item.className = "list-item";
        if (student.id === selectedValue) {
          item.classList.add("selected");
        }
        item.textContent = student.prenom;
        item.addEventListener("click", () => {
          selectedValue = student.id;
          trigger.textContent = student.prenom;
          popover.classList.remove("open");
        });
        list.appendChild(item);
      });
    }

    // Événements
    trigger.addEventListener("click", () => {
      popover.classList.toggle("open");
      renderList();
    });

    searchInput.addEventListener("input", (e) => {
      renderList(e.target.value);
    });

    document.addEventListener("click", (e) => {
      if (!popover.contains(e.target)) {
        popover.classList.remove("open");
      }
    });

    // Initialisation
    fetchStudents();
  </script>
</body>
</html>
