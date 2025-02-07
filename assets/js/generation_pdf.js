function capitalizeFirstLetter(str) {
  if (typeof str !== 'string') return ''; // Handle edge case if str is not a string
  return str.charAt(0).toUpperCase() + str.slice(1);
}
var t = ['', 'mille', 'millions', 'milliards']
var wahadat = ['', 'un', 'deux', 'troix', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf']
var whadatMabin10w20 = ['dix', 'onze', 'douze', 'treise', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf']
var acharat = ['vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vinght', 'quatre-vinght']

function transfere_nbr_lettre(nbr) {
  var inp = -1
  for (var i = 0; i < nbr.length; i++) {
    if (nbr[i] == "," || nbr[i] == ".") {
      inp = i
      break
    }
  }
  if (inp == -1) {
    return (avantVirgule(nbr))
  }
  if (inp == nbr[nbr.length - 1]) {
    return (avantVirgule(nbr.slice(nbr.length - 2, nbr.length)))
  }
  if (inp != -1 && inp != nbr[nbr.length - 1]) {
    var avant_virgule = nbr.slice(0, inp)
    var apres_virgule = nbr.slice(inp + 1, nbr.length)
    var res = avantVirgule(avant_virgule) + apresVirgule(apres_virgule)
    return (res)
  }
}

function apresVirgule(nbr) {
  var inp = -1
  var res = " virgule "
  for (var i = 0; i < nbr.length; i++) {
    if (nbr[i] != "0") {
      inp = i
      break
    }
  }
  if (inp == -1) {
    return ""
  } else {
    for (var j = 0; j < inp; j++) {
      res += "zero "
    }
    for (var i = 0; i < nbr.length; i++) {
      if (nbr[nbr.length - (i + 1)] != "0") {
        break
      }
    }
    var ff = nbr.slice(inp, nbr.length - i)
    return res += avantVirgule(String(ff))
  }
}

function avantVirgule(nbr) {
  if (nbr.length >= 0 && nbr.length <= 3) {
    if (mi2at(nbr) == "") {
      return ("zero")
    } else {
      return mi2at(nbr)
    }
  } else {
    var res = ""
    j = nbr.length
    k = 0
    var espace = 0
    while (k < parseInt(nbr.length / 3)) {
      const firstPart = nbr.slice(j - 3, j);
      if (mi2at(firstPart) != '') {
        if (mi2at(firstPart) === "un" && t[k] != "mille") {
          res = mi2at(firstPart) + " " + (t[k].slice(0, (t[k].length - 1))) + " " + res
        } else {
          res = mi2at(firstPart) + " " + t[k] + " " + res
          espace += 2
        }
      }
      if (mi2at(firstPart) == '') {
        res = mi2at(firstPart) + " " + res
      }
      k += 1
      j = j - 3
    }
    if (nbr.length % 3 == 0) {
      if (res == ' ' * espace) {
        return "zero"
      } else {
        return res
      }
    } else {
      const firstPart = nbr.slice(0, j);
      if (mi2at(firstPart) != "") {
        if (mi2at(firstPart) === "un" && t[k] != "mille") {
          res = mi2at(firstPart) + " " + (t[k].slice(0, (t[k].length - 1))) + " " + res
        } else {
          res = mi2at(firstPart) + " " + t[k] + " " + res
          espace += 2
        }
        if (mi2at(firstPart) == '') {
          if (res == ' ' * espace) {
            return "zero"
          } else {
            return res
          }
        }
        if (res == ' ' * espace) {
          return "zero"
        } else {
          return res
        }
      } else {
        if (res == ' ' * espace) {
          return "zero"
        } else {
          return res
        }
      }

    }
  }
}

function wahadatt(nbr) {
  var n = Number(nbr)
  if (n === 0) {
    return ""
  } else {
    return wahadat[n]
  }
}

function acharatt(nbr) {
  if (nbr[0] === "1") {
    return whadatMabin10w20[Number(nbr[1])]
  } else {
    if (nbr[0] === "0") {
      var res = wahadatt(nbr[1])
      return res
    } else {
      var res = acharat[Number(nbr[0]) - 2]
      if (nbr[1] == "0") {
        if (nbr[0] == "9") {
          res += "-dix"
          return res
        } else {
          return res
        }
      } else {
        if (nbr[0] == "8") {
          res += "-" + wahadat[Number(nbr[1])]
          return res
        }
        if (nbr[0] == "9") {
          res += "-" + whadatMabin10w20[Number(nbr[1])]
          return res
        }
        if (nbr[0] != "8" && nbr[0] != "9") {
          if (nbr[1] == "1") {
            res += " et un"
            return res
          } else {
            res += " " + wahadat[Number(nbr[1])]
            return res
          }
        }
      }

    }
  }
}

function mi2at(nbr) {
  if (nbr.length === 3) {
    if (nbr[0] != "0") {
      if (wahadatt(Number(nbr[0])) === "un") {
        var res = " cent"
      } else {
        var res = wahadatt(Number(nbr[0])) + " cent"
      }
      nb = nbr[1] + nbr[2]
      res += " " + acharatt(nb)
      return res
    }
    if (nbr[0] == "0") {
      var res = acharatt(nbr[1] + nbr[2])
      return res
    }

  }
  if (nbr.length === 2) {
    var res = acharatt(nbr)
    return res
  }
  if (nbr.length === 1) {
    var res = wahadatt(nbr)
    return res
  }
}

function genererRecuPaiement(paiement) {
  // Prepare data for PDF generation
  var currentDate = new Date();
  var dateRecu = currentDate.toLocaleDateString();

  // Generate random receipt number
  var num_r = Math.floor(Math.random() * 10000) + 1;

  // Calculate TVA and total
  var prix = Number(paiement.data.montant_final);
  var tva = prix * 0.2;
  var total = prix + tva;

  // Convert total to words
  var montantEnLettre = capitalizeFirstLetter(transfere_nbr_lettre(String(total)));

  // Create PDF content with professional styling
  var htmlRecu = `
  <style>
      body { font-family: 'Arial', sans-serif; }
      .invoice-container {
          width: 90%;
          margin: 0 auto;
          border: 1px solid #e0e0e0;
          padding: 20px;
          box-shadow: 0 0 10px rgba(0,0,0,0.1);
      }
      .invoice-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
          border-bottom: 2px solid #primary;
          padding-bottom: 10px;
      }
      .invoice-logo {
          max-width: 200px;
      }
      .invoice-details {
          text-align: right;
      }
      .invoice-table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 20px;
      }
      .invoice-table th, .invoice-table td {
          border: 1px solid #e0e0e0;
          padding: 10px;
          text-align: left;
      }
      .invoice-total {
          text-align: right;
          margin-bottom: 20px;
      }
      .invoice-footer {
          font-size: 0.8em;
          color: #666;
          text-align: center;
      }
  </style>
  <div class="invoice-container">
      <div class="invoice-header">
          <img src="../../assets/img/school/school.png" alt="School Logo" class="invoice-logo">
          <div class="invoice-details">
              <h2>Reçu de Paiement</h2>
              <p>Numéro de reçu: ${num_r}</p>
              <p>Date: ${dateRecu}</p>
          </div>
      </div>
      
      <table class="invoice-table">
          <tr>
              <th>Élève</th>
              <th>Filière</th>
              <th>Niveau</th>
              <th>Type de Frais</th>
          </tr>
          <tr>
              <td>${paiement.data.nom_eleve} ${paiement.data.prenom_eleve}</td>
              <td>${paiement.data.nom_filiere}</td>
              <td>${paiement.data.nom_niveau}</td>
              <td>${paiement.data.type_frais}</td>
          </tr>
      </table>
      
      <table class="invoice-table">
          <tr>
              <th>Période</th>
              <th>Montant de Base</th>
              <th>Réduction</th>
              <th>Montant Final</th>
          </tr>
          <tr>
              <td>${paiement.data.nom_periode}</td>
              <td>${paiement.data.tarif_montant_base} MAD</td>
              <td>${paiement.data.reduction_appliquee} MAD</td>
              <td>${paiement.data.montant_final} MAD</td>
          </tr>
      </table>
      
      <div class="invoice-total">
          <p><strong>Sous-Total HT:</strong> ${prix} MAD</p>
          <p><strong>TVA (20%):</strong> ${tva.toFixed(2)} MAD</p>
          <p><strong>Total TTC:</strong> ${total.toFixed(2)} MAD</p>
      </div>
      
      <p class="invoice-footer">
          Montant en lettres: <strong>${montantEnLettre} Dirhams</strong>
      </p>
      
      <div class="invoice-footer">
          <p>Merci de conserver ce reçu</p>
          <p>École Professionnelle - Établissement Officiel</p>
      </div>
  </div>
  `;

  // Generate PDF
  html2pdf()
    .set({
      margin: [10, 10, 10, 10],
      filename: `Recu_Paiement_${paiement.data.nom_eleve}_${currentDate.getTime()}.pdf`,
      image: {
        type: 'jpeg',
        quality: 0.98
      },
      html2canvas: {
        scale: 4,
        logging: false
      },
      jsPDF: {
        unit: 'mm',
        format: 'a4',
        orientation: 'portrait'
      }
    })
    .from(htmlRecu)
    .save();
}

// Function to fetch payment details and generate PDF
function genererPDFPaiement(id_paiement) {
  // AJAX call to fetch payment details
  $.ajax({
    url: 'getPaiementDetails.php', // You'll need to create this PHP script
    type: 'POST',
    data: {
      id_paiement: id_paiement
    },
    dataType: 'json',
    success: function(paiement) {
      console.log("Données récupérées :", paiement);
      if (paiement) {

        genererRecuPaiement(paiement);
      } else {
        alert('Détails du paiement non trouvés');
      }
    },
    error: function() {
      alert('Erreur de récupération des détails du paiement');
    }
  });
}