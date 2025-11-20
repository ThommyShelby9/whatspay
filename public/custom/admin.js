// Configuration globale CSRF pour jQuery
$.ajaxSetup({
  headers: {
    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
  },
});

let uploadedFiles = [];

var localities = "";
var localitiesJson = document.getElementById("localitiesJson");
if (localitiesJson !== undefined && localitiesJson !== null) {
  localities = JSON.parse($("#localitiesJson").val());
  $("#localitiesJson").val("");
}

var countries = "";
var countriesJson = document.getElementById("countriesJson");
if (countriesJson !== undefined && countriesJson !== null) {
  countries = JSON.parse($("#countriesJson").val());
  $("#countriesJson").val("");
}

var contenttypes = "";
var contenttypesJson = document.getElementById("contenttypesJson");
if (contenttypesJson !== undefined && contenttypesJson !== null) {
  contenttypes = JSON.parse($("#contenttypesJson").val());
  $("#contenttypesJson").val("");
}

var categories = "";
var categoriesJson = document.getElementById("categoriesJson");
if (categoriesJson !== undefined && categoriesJson !== null) {
  categories = JSON.parse($("#categoriesJson").val());
  $("#categoriesJson").val("");
}

var studies = "";
var studiesJson = document.getElementById("studiesJson");
if (studiesJson !== undefined && studiesJson !== null) {
  studies = JSON.parse($("#studiesJson").val());
  $("#studiesJson").val("");
}

$(document).ready(function () {
  var bjId = "";
  var bjIdField = document.getElementById("bjId");
  if (bjIdField !== undefined && bjIdField !== null) {
    bjId = $("#bjId").val();
  }

  var country = document.getElementById("country");
  if (country !== undefined && country !== null) {
    $("#country").on("change", function (event) {
      event.preventDefault();
      var content = "";
      if ($("#country").val() === bjId) {
        $.each(localities, function (i, item) {
          content +=
            '<option value="' + item.id + '">' + item.name + "</option>";
        });
      } else {
        $.each(countries, function (i, item) {
          if ($("#country").val() === item.id) {
            content = '<option value="">' + item.name + "</option>";
          }
        });
      }
      $("#locality").html(content);
    });
  }

  var login_submit_button = document.getElementById("login_submit_button");
  if (login_submit_button !== undefined && login_submit_button !== null) {
    $("#login_submit_button").click(function () {
      $("#login_form").submit();
    });
  }

  var registration_submit_button = document.getElementById(
    "registration_submit_button"
  );
  if (
    registration_submit_button !== undefined &&
    registration_submit_button !== null
  ) {
    $("#registration_submit_button").click(function () {
      $("#registration_form").submit();
    });
  }

  var forgottenpassword_submit_button = document.getElementById(
    "forgottenpassword_submit_button"
  );
  if (
    forgottenpassword_submit_button !== undefined &&
    forgottenpassword_submit_button !== null
  ) {
    $("#forgottenpassword_submit_button").click(function () {
      $("#forgottenpassword_form").submit();
    });
  }

  var task_submit_Button = document.getElementById("task_submit_Button");
  if (task_submit_Button !== undefined && task_submit_Button !== null) {
    $("#task_submit_Button").click(function () {
      $("#taskfiles").val(JSON.stringify(uploadedFiles));
      $("#task_form").submit();
    });
  }

  var whatsappNumberGenerateCodeButton = document.getElementById(
    "whatsappNumberGenerateCodeButton"
  );
  if (
    whatsappNumberGenerateCodeButton !== undefined &&
    whatsappNumberGenerateCodeButton !== null
  ) {
    $("#whatsappNumberGenerateCodeButton").click(function () {
      let valid = true;
      let fields = [
        {
          field: "phone",
          message: "Veuillez bien saisir le numero de telephone",
        },
      ];
      $.each(fields, function (i, item) {
        if (
          $("#" + item.field)
            .val()
            .replace(/ /g, "") === ""
        ) {
          valid = false;
          $.notify(item.message, "error");
          $("#" + item.field).focus();
        }
      });
      if (valid === true) {
        var json = "";
        json = JSON.stringify({
          session: $("#session").val(),
          phonecountry: $("#phonecountry").val(),
          phone: $("#phone").val(),
        });
        var url = $("#baseUrl").val() + "/api/whatsapp/generatecode";
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "" + url);
        xhr.setRequestHeader("Content-type", "application/json; charset=utf-8");
        $("#whatsappNumberGenerateCodeButton").attr("disabled", "disabled");
        $("#phonecountry").attr("disabled", "disabled");
        $("#phone").attr("disabled", "disabled");
        $.notify("Traitement en cours", "warn");
        xhr.send(json);
        xhr.onload = function () {};
        xhr.onerror = function () {};
        xhr.onprogress = function (event) {};
        xhr.onreadystatechange = function () {
          if (xhr.readyState !== 4) return false;
          if (xhr.status !== 200) {
            //console.log(xhr.response);
            try {
              var response = JSON.parse(xhr.response);
              if (response.error_message !== undefined) {
                $.notify(response.error_message, "error");
              } else {
                $.notify("Une erreur est survenue", "error");
              }
            } catch (err) {
              $.notify("Une erreur est survenue", "error");
            }
            $("#whatsappNumberGenerateCodeButton").removeAttr("disabled");
            $("#phonecountry").removeAttr("disabled");
            $("#phone").removeAttr("disabled");
            return false;
          } else {
            console.log(xhr.response);
            var response = JSON.parse(xhr.response);
            if (response.error === false) {
              $.notify(response.error_message, "success");
              //$('#whatsappNumberGenerateCodeButton').removeAttr('disabled')
              $("#codeValidationDiv").attr("style", "");
              $("#whatsappNumberId").val(response.operator_response.pId);
            } else {
              $.notify(response.error_message, "error");
              $("#whatsappNumberGenerateCodeButton").removeAttr("disabled");
              $("#phonecountry").removeAttr("disabled");
              $("#phone").removeAttr("disabled");
            }
          }
        };
      }
    });
  }

  var whatsappNumberValidateCodeButton = document.getElementById(
    "whatsappNumberValidateCodeButton"
  );
  if (
    whatsappNumberValidateCodeButton !== undefined &&
    whatsappNumberValidateCodeButton !== null
  ) {
    $("#whatsappNumberValidateCodeButton").click(function () {
      let valid = true;
      let fields = [
        {
          field: "phone",
          message: "Veuillez bien saisir le numero de telephone",
        },
        {
          field: "code",
          message: "Veuillez bien saisir le code de validation",
        },
      ];
      $.each(fields, function (i, item) {
        if (
          $("#" + item.field)
            .val()
            .replace(/ /g, "") === ""
        ) {
          valid = false;
          $.notify(item.message, "error");
          $("#" + item.field).focus();
        }
      });
      if (valid === true) {
        var json = "";
        json = JSON.stringify({
          session: $("#session").val(),
          phonecountry: $("#phonecountry").val(),
          phone: $("#phone").val(),
          code: $("#code").val(),
          whatsappNumberId: $("#whatsappNumberId").val(),
        });
        var url = $("#baseUrl").val() + "/api/whatsapp/validatecode";
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "" + url);
        xhr.setRequestHeader("Content-type", "application/json; charset=utf-8");
        $("#whatsappNumberGenerateCodeButton").attr("disabled", "disabled");
        $("#phonecountry").attr("disabled", "disabled");
        $("#phone").attr("disabled", "disabled");
        $("#whatsappNumberValidateCodeButton").attr("disabled", "disabled");
        $("#code").attr("disabled", "disabled");
        $.notify("Traitement en cours", "warn");
        xhr.send(json);
        xhr.onload = function () {};
        xhr.onerror = function () {};
        xhr.onprogress = function (event) {};
        xhr.onreadystatechange = function () {
          if (xhr.readyState !== 4) return false;
          if (xhr.status !== 200) {
            //console.log(xhr.response);
            try {
              var response = JSON.parse(xhr.response);
              if (response.error_message !== undefined) {
                $.notify(response.error_message, "error");
              } else {
                $.notify("Une erreur est survenue", "error");
              }
            } catch (err) {
              $.notify("Une erreur est survenue", "error");
            }
            $("#whatsappNumberGenerateCodeButton").removeAttr("disabled");
            $("#phonecountry").removeAttr("disabled");
            $("#phone").removeAttr("disabled");
            $("#whatsappNumberValidateCodeButton").removeAttr("disabled");
            $("#code").removeAttr("disabled");
            return false;
          } else {
            console.log(xhr.response);
            var response = JSON.parse(xhr.response);
            if (response.error === false) {
              $.notify(response.error_message, "success");
              //console.log($('#baseUrl').val()+'/admin/whatsappnumbers')
              //$('#whatsappNumberGenerateCodeButton').removeAttr('disabled')
              var timeleft = 2;
              var downloadTimer = setInterval(function () {
                if (timeleft <= 0) {
                  clearInterval(downloadTimer);
                  document.location.href =
                    $("#baseUrl").val() + "/admin/whatsappnumbers";
                  return false;
                }
                timeleft -= 1;
              }, 1000);
            } else {
              $.notify(response.error_message, "error");
              $("#whatsappNumberGenerateCodeButton").removeAttr("disabled");
              $("#phonecountry").removeAttr("disabled");
              $("#phone").removeAttr("disabled");
              $("#whatsappNumberValidateCodeButton").removeAttr("disabled");
              $("#code").removeAttr("disabled");
            }
          }
        };
      }
    });
  }

  var categoriesDiv = document.getElementById("categoriesDiv");
  var profil = document.getElementById("profil");
  if (
    categoriesDiv !== undefined &&
    categoriesDiv !== null &&
    profil !== undefined &&
    profil !== null
  ) {
    $("#profil").on("change", function (event) {
      event.preventDefault();
      if ($("#profil").val() === "DIFFUSEUR") {
        $("#categoriesDiv").attr("style", "");
        $("#vuesmoyenDiv").attr("style", "");
        $("#langDiv").attr("style", "");
        $("#contenttypeDiv").attr("style", "");
        $("#studyDiv").attr("style", "");
        $("#occupationDiv").attr("style", "");
      } else {
        $("#categoriesDiv").attr("style", "display:none");
        $("#vuesmoyenDiv").attr("style", "display:none");
        $("#langDiv").attr("style", "display:none");
        $("#contenttypeDiv").attr("style", "display:none");
        $("#studyDiv").attr("style", "display:none");
        $("#occupationDiv").attr("style", "display:none");
      }
    });
    if ($("#linkprofile").val() === "DIFFUSEUR") {
      $("#categoriesDiv").attr("style", "");
      $("#vuesmoyenDiv").attr("style", "");
      $("#langDiv").attr("style", "");
      $("#contenttypeDiv").attr("style", "");
      $("#studyDiv").attr("style", "");
      $("#occupationDiv").attr("style", "");
    } else {
      $("#categoriesDiv").attr("style", "display:none");
      $("#vuesmoyenDiv").attr("style", "display:none");
      $("#langDiv").attr("style", "display:none");
      $("#contenttypeDiv").attr("style", "display:none");
      $("#studyDiv").attr("style", "display:none");
      $("#occupationDiv").attr("style", "display:none");
    }
    $("#occupation").on("change", function (event) {
      event.preventDefault();
      if ($("#occupation").val() === "") {
        $("#autre_occupationDiv").attr("style", "margin-top: 10px;");
      } else {
        $("#autre_occupationDiv").attr(
          "style",
          "margin-top: 10px; display:none"
        );
      }
    });
  }

  var dropZoneDiv = document.getElementById("dropZoneDiv");
  if (dropZoneDiv !== undefined && dropZoneDiv !== null) {
    createUploadForm("filesForm", "dropZoneDiv");
  }

  var filtre_country = document.getElementById("filtre_country");
  var filtre_locality = document.getElementById("filtre_locality");
  if (filtre_country !== undefined && filtre_country !== null) {
    $("#filtre_country").on("change", function (event) {
      event.preventDefault();
      var content = '<option value="all">Toutes les localites</option>';
      if ($("#filtre_country").val() === bjId) {
        $.each(localities, function (i, item) {
          content +=
            '<option value="' + item.id + '">' + item.name + "</option>";
        });
      }
      if (filtre_locality !== undefined && filtre_locality !== null) {
        $("#filtre_locality").html(content);
      }
    });
  }

  var filtre_category = document.getElementById("filtre_category");
  if (filtre_category !== undefined && filtre_category !== null) {
    $("#filtre_category").select2({
      theme: "bootstrap-5",
      width: $(this).data("width")
        ? $(this).data("width")
        : $(this).hasClass("w-100")
        ? "100%"
        : "style",
      placeholder: $(this).data("placeholder"),
      closeOnSelect: false,
    });
  }

  var filtre_contenu = document.getElementById("filtre_contenu");
  if (filtre_contenu !== undefined && filtre_contenu !== null) {
    $("#filtre_contenu").select2({
      theme: "bootstrap-5",
      width: $(this).data("width")
        ? $(this).data("width")
        : $(this).hasClass("w-100")
        ? "100%"
        : "style",
      placeholder: $(this).data("placeholder"),
      closeOnSelect: false,
    });
  }

  var filtre_study = document.getElementById("filtre_study");
  if (filtre_study !== undefined && filtre_study !== null) {
    $("#filtre_study").select2({
      theme: "bootstrap-5",
      width: $(this).data("width")
        ? $(this).data("width")
        : $(this).hasClass("w-100")
        ? "100%"
        : "style",
      placeholder: $(this).data("placeholder"),
      closeOnSelect: false,
    });
  }

  var filtre_occupation = document.getElementById("filtre_occupation");
  if (filtre_occupation !== undefined && filtre_occupation !== null) {
    $("#filtre_occupation").select2({
      theme: "bootstrap-5",
      width: $(this).data("width")
        ? $(this).data("width")
        : $(this).hasClass("w-100")
        ? "100%"
        : "style",
      placeholder: $(this).data("placeholder"),
      closeOnSelect: false,
    });
  }

  var filtre_lang = document.getElementById("filtre_lang");
  if (filtre_lang !== undefined && filtre_lang !== null) {
    $("#filtre_lang").select2({
      theme: "bootstrap-5",
      width: $(this).data("width")
        ? $(this).data("width")
        : $(this).hasClass("w-100")
        ? "100%"
        : "style",
      placeholder: $(this).data("placeholder"),
      closeOnSelect: false,
    });
  }

  var items_datatable = document.getElementById("items_datatable");
  if (items_datatable !== undefined && items_datatable !== null) {
    $("#items_datatable").DataTable({
      dom: "Bfrtip",
      buttons: ["copyHtml5", "excelHtml5", "csvHtml5", "pdfHtml5"],
    });
  }
});

//https://apalfrey.github.io/select2-bootstrap-5-theme/examples/multiple-select/

function showToast(message, type) {
  $.notify(message, type); //error ; success ; warn ; info
}

function displayToast(type, message) {
  let uuid = crypto.randomUUID();
  var html =
    '<div class="toast hide toast fade" id="' +
    uuid +
    '" role="alert" aria-live="assertive" aria-atomic="true">\n' +
    '                        <div class="d-flex justify-content-between alert-' +
    type +
    '">\n' +
    '                          <div class="toast-body">' +
    message +
    "</div>\n" +
    '                          <button class="btn-close btn-close-white me-2 m-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>\n' +
    "                        </div>\n" +
    "                      </div>";
  $("#toastDiv").append(html);
  const content = document.getElementById(uuid);
  const toast = new bootstrap.Toast(content);
  toast.show();
}

function numerique(event) {
  var key = window.event ? event.keyCode : event.which;
  if (
    event.keyCode === 8 ||
    event.keyCode === 46 ||
    event.keyCode === 37 ||
    event.keyCode === 39
  ) {
    return true;
  } else if (key < 48 || key > 57) {
    return false;
  } else {
    return true;
  }
}

function numeriqueNumberOnly(event) {
  var key = window.event ? event.keyCode : event.which;
  if (key < 48 || key > 57) {
    return false;
  } else {
    return true;
  }
}

function isEmail(email) {
  var re =
    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}

function acceptUpload(file, done) {
  done();
}

function finalizeUpload(response, type) {
  if (response.error === false) {
    $.each(response.files, function (i, item) {
      switch (parseInt(type)) {
        case 1:
          uploadedFiles.push(item);
          break;
      }
    });
  }
  console.log(JSON.stringify(uploadedFiles));
}

function createUploadForm(
  id,
  uploadRow,
  acceptedFiles = "image/*,application/pdf,.psd,.zip,.mp3,.m4a,.m4b,.m4p,.docs,.docx,.xls,.xlsx",
  type = 1
) {
  var uploadForm =
    '<form class="dropzone dropzone-primary" id="' +
    id +
    '" action="' +
    $("#baseUrl").val() +
    '/api/upload"><div class="dz-message needsclick"><i class="icon-cloud-up"></i>' +
    '<h6><span data-i18n="drop_file"></span></h6><span class="note needsclick"><span data-i18n="drop_file_note"></span></span></div></form>';
  document.getElementById(uploadRow).innerHTML = uploadForm;
  new Dropzone("#" + id, {
    url: $("#baseUrl").val() + "/api/upload",
    paramName: "files",
    maxFiles: 10,
    maxFilesize: 10,
    acceptedFiles: acceptedFiles,
    dictDefaultMessage: "Déposer des fichiers ici pour les télécharger",
    dictFallbackMessage:
      "Votre navigateur ne prend pas en charge les téléchargements de fichiers par glisser-déposer.",
    dictFileTooBig:
      "Fichier volumineux ({{filesize}}MiB). Taille maximale: {{maxFilesize}}MiB.",
    dictInvalidFileType: "Vous ne pouvez pas télécharger ce type de fichiers.",
    dictResponseError: "Le serveur a répondu avec le code {{statusCode}} .",
    dictCancelUpload: "Annuler téléchargement",
    dictUploadCanceled: "Téléchargement annulé.",
    dictCancelUploadConfirmation:
      "Voulez-vous vraiment annuler ce téléchargement ?",
    dictRemoveFile: "Supprimer le fichier",
    dictMaxFilesExceeded: "Vous ne pouvez plus télécharger de fichiers.",
    accept: function (file, done) {
      acceptUpload(file, done);
    },
    init: function () {
      this.on("success", function (file, response) {
        console.log(response);
        finalizeUpload(response, type);
      });
      this.on("addedfile", function (file) {
        console.log("Added file.");
      });
      this.on("error", function (file, errorMessage) {
        console.log(errorMessage);
      });
      this.on("processing", function (file) {
        console.log("processing");
      });
      this.on("uploadprogress", function (file) {
        console.log("uploadprogress");
      });
      this.on("sending", function (file) {
        console.log("sending.");
      });
      this.on("success", function (file, response) {
        console.log(response);
      });
      this.on("complete   ", function (file) {
        console.log("complete ");
      });
      this.on("canceled", function (file) {
        console.log("canceled");
      });
      this.on("successmultiple", function (file) {
        console.log("successmultiple");
      });
    },
  });
}

var currentTab = 0;
var wizardtypevalue = "";
var wizardtype = document.getElementById("wizardtype");
if (wizardtype !== undefined && wizardtype !== null) {
  wizardtypevalue = $("#wizardtype").val();
  if (wizardtypevalue === "registration") {
    showTab(currentTab);
  }
}

function showTab(n) {
  var x = document.getElementsByClassName("tab");
  x[n].style.display = "block";
  if (n === 0) {
    document.getElementById("prevBtn").style.display = "none";
  } else {
    document.getElementById("prevBtn").style.display = "inline";
  }
  if (n === x.length - 1) {
    document.getElementById("nextBtn").innerHTML = "Enregistrer";
  } else {
    document.getElementById("nextBtn").innerHTML = "Suivant";
  }
  fixStepIndicator(n);
}

function nextPrev(n, form) {
  var x = document.getElementsByClassName("tab");
  if (n === 1 && !validateForm()) return false;
  x[currentTab].style.display = "none";
  currentTab = currentTab + n;
  if (currentTab >= x.length) {
    document.getElementById("prevBtn").style.display = "none";
    document.getElementById("nextBtn").style.display = "none";
    document.getElementById(form).submit();
    return false;
  }
  showTab(currentTab);
}

function isNumber(value) {
  return typeof value === "number";
}

function validateForm() {
  var valid = true;

  switch (wizardtypevalue) {
    case "registration":
      switch (currentTab) {
        case 0:
          let fields = [
            { field: "prenom", message: "Veuillez bien indiquer votre prenom" },
            { field: "nom", message: "Veuillez bien indiquer votre nom" },
            { field: "phone", message: "Veuillez bien indiquer votre contact" },
            {
              field: "email",
              message: "Veuillez bien indiquer votre adresse mail",
            },
            {
              field: "password",
              message: "Veuillez bien indiquer votre mot de passe",
            },
            {
              field: "password_confirmation",
              message: "Veuillez bien confirmer votre mot de passe",
            },
          ];
          $.each(fields, function (i, item) {
            if (
              $("#" + item.field)
                .val()
                .replace(/ /g, "") === ""
            ) {
              valid = false;
              $.notify(item.message, "error");
              $("#" + item.field).focus();
            }
          });
          if (valid === true) {
            if (
              $("#email").val().replace(/ /g, "") !== "" &&
              !isEmail($("#email").val().replace(/ /g, ""))
            ) {
              valid = false;
              $.notify("Email incorrect", "error");
              $("#email").focus();
            } else if (
              $("#password").val() !== $("#password_confirmation").val()
            ) {
              valid = false;
              $.notify("Les mots de passe ne correspondent pas", "error");
              $("#password_confirmation").focus();
            }
          }
          break;
        case 1:
          let fields2 = [
            { field: "profil", message: "Veuillez bien indiquer votre profil" },
          ];
          $.each(fields2, function (i, item) {
            if (
              $("#" + item.field)
                .val()
                .replace(/ /g, "") === ""
            ) {
              valid = false;
              $.notify(item.message, "error");
              $("#" + item.field).focus();
            }
          });
          if (valid === true) {
            let fields3 = [
              {
                field: "vuesmoyen",
                message: "Veuillez bien indiquer votre nombre de vues moyen",
              },
            ];
            $.each(fields3, function (i, item) {
              if (
                $("#" + item.field)
                  .val()
                  .replace(/ /g, "") === ""
              ) {
                valid = false;
                $.notify(item.message, "error");
                $("#" + item.field).focus();
              }
            });
            if ($("#profil").val() === "DIFFUSEUR") {
              if ($("#occupation").val() === "") {
                let fields3 = [
                  {
                    field: "autre_occupation",
                    message: "Veuillez bien saisir votre profession",
                  },
                ];
                $.each(fields3, function (i, item) {
                  if (
                    $("#" + item.field)
                      .val()
                      .replace(/ /g, "") === ""
                  ) {
                    valid = false;
                    $.notify(item.message, "error");
                    $("#" + item.field).focus();
                  }
                });
              }
              var nbC = 0;
              var nbCt = 0;
              $.each(categories, function (i, item) {
                if (document.getElementById("c_" + item.id).checked === true) {
                  nbC = nbC + 1;
                }
              });
              $.each(contenttypes, function (i, item) {
                if (document.getElementById("ct_" + item.id).checked === true) {
                  nbCt = nbCt + 1;
                }
              });

              if (nbC === 0) {
                valid = false;
                $.notify(
                  "Veuillez bien indiquer au moins une categorie de publication",
                  "error"
                );
              }

              if (nbCt === 0) {
                valid = false;
                $.notify(
                  "Veuillez bien indiquer au moins un type de contenu",
                  "error"
                );
              }
            }
          }

          break;
        case 2:
          if (document.getElementById("termes").checked !== true) {
            valid = false;
            $.notify(
              "Veuillez bien accepter les termes et conditions",
              "error"
            );
            $("#birthday").focus();
          }
          break;
      }
      break;
  }

  if (valid) {
    document.getElementsByClassName("step")[currentTab].className += " finish";
  }
  return valid;
}

function fixStepIndicator(n) {
  var i,
    x = document.getElementsByClassName("step");
  for (i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(" active", "");
  }
  x[n].className += " active";
}

//Scripts additonnels
let viewsClicksChart = null;
let deviceChart = null;
let weekdayChart = null;
let geoMap = null;

function initializeCharts() {
  const stats = window.GLOBAL_STATS || {};

  const dailyData = stats.daily_data || {};
  const dailyLabels = dailyData.dates || [
    "Lun",
    "Mar",
    "Mer",
    "Jeu",
    "Ven",
    "Sam",
    "Dim",
  ];
  const dailyViews = dailyData.views || Array(dailyLabels.length).fill(0);
  const dailyClicks = dailyData.clicks || Array(dailyLabels.length).fill(0);

  // Views & Clicks Evolution Chart
  const viewsClicksOptions = {
    series: [
      {
        name: "Vues",
        data: dailyViews,
      },
      {
        name: "Clics",
        data: dailyClicks,
      },
    ],
    chart: {
      type: "area",
      height: 300,
      fontFamily: "inherit",
      toolbar: {
        show: true,
        tools: {
          download: true,
          selection: true,
          zoom: true,
          zoomin: true,
          zoomout: true,
          pan: true,
          reset: true,
        },
      },
      zoom: {
        enabled: true,
      },
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      curve: "smooth",
      width: 2,
    },
    colors: ["#3b82f6", "#10b981"],
    fill: {
      type: "gradient",
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.7,
        opacityTo: 0.2,
        stops: [0, 100],
      },
    },
    xaxis: {
      categories: dailyLabels,
      type: "category",
    },
    yaxis: {
      title: {
        text: "Nombre",
      },
    },
    tooltip: {
      shared: true,
      intersect: false,
      theme: "light",
    },
    legend: {
      position: "top",
      horizontalAlign: "right",
    },
  };

  const viewsClicksElement = document.querySelector("#views-clicks-chart");

  if (viewsClicksElement) {
    if (viewsClicksChart) {
      viewsClicksChart.destroy();
    }
    viewsClicksChart = new ApexCharts(viewsClicksElement, viewsClicksOptions);
    viewsClicksChart.render();
  }

  // Device Distribution Chart
  const devices = stats.devices || {
    desktop: 0,
    mobile: 0,
    tablet: 0,
    unknown: 0,
  };
  const deviceLabels = ["Desktop", "Mobile", "Tablette", "Autres"];
  const deviceValues = [
    devices.desktop || 0,
    devices.mobile || 0,
    devices.tablet || 0,
    devices.unknown || 0,
  ];
  const deviceOptions = {
    series: deviceValues,
    chart: {
      type: "donut",
      height: 280,
      fontFamily: "inherit",
      toolbar: {
        show: true,
        tools: {
          download: true,
        },
      },
    },
    labels: deviceLabels,
    colors: ["#3b82f6", "#10b981", "#06b6d4", "#6b7280"],
    legend: {
      position: "bottom",
      fontFamily: "inherit",
      fontSize: "13px",
    },
    plotOptions: {
      pie: {
        donut: {
          size: "65%",
          labels: {
            show: true,
            name: {
              fontSize: "12px",
            },
            value: {
              fontSize: "14px",
              fontWeight: 600,
            },
          },
        },
      },
    },
    dataLabels: {
      enabled: true,
      formatter: function (val) {
        return val.toFixed(1) + "%";
      },
    },
  };

  const deviceElement = document.querySelector("#device-chart");
  if (deviceElement) {
    if (deviceChart) {
      deviceChart.destroy();
    }
    deviceChart = new ApexCharts(deviceElement, deviceOptions);
    deviceChart.render();
  }

  // Weekday Traffic Chart
  const weekdayData = stats.weekday_data;
  const weekdayLabels = Object.keys(weekdayData);
  const weekdayValues = Object.values(weekdayData);
  const weekdayOptions = {
    series: [
      {
        name: "Clics",
        data: weekdayValues,
      },
    ],
    chart: {
      type: "bar",
      height: 280,
      fontFamily: "inherit",
      toolbar: {
        show: true,
        tools: {
          download: true,
          selection: true,
          zoom: true,
          zoomin: true,
          zoomout: true,
          pan: true,
          reset: true,
        },
      },
    },
    plotOptions: {
      bar: {
        columnWidth: "55%",
        borderRadius: 4,
        dataLabels: {
          position: "top",
        },
      },
    },
    colors: ["#06b6d4"],
    xaxis: {
      categories: weekdayLabels,
      title: {
        text: "Jour de la semaine",
      },
    },
    yaxis: {
      title: {
        text: "Nombre de clics",
      },
    },
    dataLabels: {
      enabled: true,
      offsetY: -20,
      style: {
        fontSize: "12px",
        colors: ["#06b6d4"],
      },
    },
    legend: {
      position: "top",
      horizontalAlign: "right",
    },
  };

  const weekdayElement = document.querySelector("#weekday-chart");
  if (weekdayElement) {
    if (weekdayChart) {
      weekdayChart.destroy();
    }
    weekdayChart = new ApexCharts(weekdayElement, weekdayOptions);
    weekdayChart.render();
  }
}

async function initializeGeoMap() {
  const geoStats = window.GLOBAL_STATS.geography || [];
  const geoElement = document.getElementById("geo-chart");
  if (!geoElement) return;

  // Détruire la carte précédente si elle existe
  if (geoMap) {
    geoMap.remove();
  }

  // Initialiser la carte centrée sur l'Afrique
  geoMap = L.map("geo-chart").setView([7.5, 2.0], 6);

  // Fond de carte OpenStreetMap
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 18,
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(geoMap);

  // Fonction pour géocoder via Nominatim
  async function geocodeCity(city, country) {
    const query = encodeURIComponent(`${city}, ${country}`);
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${query}&limit=1`;
    try {
      const response = await fetch(url);
      const data = await response.json();
      if (data.length > 0) {
        return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
      }
    } catch (error) {
      console.error("Erreur de géocodage:", error);
    }
    return null;
  }

  // Boucle sur les stats
  for (const item of geoStats) {
    const city = item.city || item.region || item.country;
    const coords = await geocodeCity(city, item.country);

    if (coords) {
      const circle = L.circle(coords, {
        color: "#3b82f6",
        fillColor: "#3b82f6",
        fillOpacity: 0.5,
        radius: 5000 * item.total, // rayon proportionnel
      }).addTo(geoMap);

      circle.bindPopup(`
                <strong>${city}</strong><br/>
                Total clics: ${item.total}
            `);
    }
  }
}

if (
  window.location.pathname.startsWith("/admin/announcer/campaigns") ||
  window.location.pathname.startsWith("/admin/task")
) {
  $(document).ready(function () {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Initialize charts
    initializeCharts();

    initializeGeoMap();

    // Copy to clipboard function
    window.copyToClipboard = function (text) {
      navigator.clipboard.writeText(text).then(
        function () {
          alert("Lien copié dans le presse-papiers !");
        },
        function (err) {
          console.error("Erreur lors de la copie : ", err);
        }
      );
    };

    // Re-initialize charts when tab is shown
    $('a[data-bs-toggle="tab"]').on("shown.bs.tab", function () {
      setTimeout(function () {
        if (viewsClicksChart) viewsClicksChart.render();
        if (deviceChart) deviceChart.render();
        if (weekdayChart) weekdayChart.render();
      }, 100);
    });

    // Event handlers
    $(".view-details").on("click", function () {
      var id = $(this).data("id");
      $(".submission-details-content").html(
        '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Chargement...</p></div>'
      );
      $("#submissionDetailsModal").modal("show");

      setTimeout(function () {
        $(".submission-details-content").html(`
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Diffuseur</h6>
                                        <p>John Doe</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Vues déclarées</h6>
                                        <p>1,234</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Date de soumission</h6>
                                        <p>01/11/2025 08:30</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Gain</h6>
                                        <p>1,234 F</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Statut</h6>
                                        <span class="badge bg-success">Terminé</span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="text-muted mb-2">Captures d'écran</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <img src="https://via.placeholder.com/300x600" class="img-fluid rounded" alt="Capture d'écran">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <img src="https://via.placeholder.com/300x600" class="img-fluid rounded" alt="Capture d'écran">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="text-muted mb-2">Commentaire</h6>
                                    <div class="p-3 bg-light rounded">
                                        <p class="mb-0">Excellente campagne avec un bon taux d'engagement.</p>
                                    </div>
                                </div>
                            </div>
                        `);
      }, 500);
    });

    $(".approve-submission").on("click", function () {
      var id = $(this).data("id");
      if (confirm("Êtes-vous sûr de vouloir approuver cette soumission ?")) {
        alert("Soumission approuvée avec succès !");
      }
    });

    $(".reject-submission").on("click", function () {
      var id = $(this).data("id");
      if (confirm("Êtes-vous sûr de vouloir rejeter cette soumission ?")) {
        alert("Soumission rejetée avec succès !");
      }
    });

    // Delete task
    $(".delete-task").on("click", function () {
      const taskId = $(this).data("task-id");
      $("#deleteTaskForm").attr("action", `/admin/task/${taskId}/delete`);
      $("#deleteTaskModal").modal("show");
    });

    function updateViews() {
      let budget = parseFloat($("#budget").val());
      let viewPrice = parseFloat($("#viewPrice").val());

      if (!isNaN(budget) && budget > 0 && !isNaN(viewPrice) && viewPrice > 0) {
        let views = Math.floor(budget / viewPrice);
        $("#viewsEstimated").text("Vues estimées : " + views);
      } else {
        $("#viewsEstimated").text("Vues estimées : 0");
      }
    }

    $("#budget, #viewPrice").on("input", updateViews);
  });
}

if (window.location.pathname == "/admin/announcer/campaigns") {
  // Delete Campaign on Annonceur Dash
  $(".delete-campaign").on("click", function () {
    console.log("ok");

    const campaignId = $(this).data("campaign-id");
    $("#deleteCampaignForm").attr(
      "action",
      `/admin/announcer/campaigns/${campaignId}`
    );
    $("#deleteCampaignModal").modal("show");
  });
}

if (window.location.pathname == "/admin/announcer/reports") {
  $(document).ready(function () {
    // Initialize DataTable
    $("#campaigns-stats-table").DataTable({
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json",
      },
      pageLength: 10,
      order: [[1, "desc"]],
    });

    const stats = window.GLOBAL_STATS || [];
    // Campaign Performance Chart
    let campaignData = stats.map((stat) => stat.total_views ?? 0);
    let campaignLabels = stats.map((stat) => stat.task_name ?? "N/A");

    if (campaignData.length === 0) {
      campaignData = [0];
      campaignLabels = ["Aucune donnée"];
    }

    var campaignChart = new ApexCharts(
      document.querySelector("#campaign-performance-chart"),
      {
        series: [
          {
            name: "Vues",
            data: campaignData,
          },
        ],
        chart: {
          type: "bar",
          height: 320,
          toolbar: {
            show: false,
          },
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: "45%",
            endingShape: "rounded",
          },
        },
        dataLabels: {
          enabled: false,
        },
        colors: ["#3b5de7"],
        xaxis: {
          categories: campaignLabels,
          labels: {
            show: true,
            trim: true,
            maxHeight: 50,
          },
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val.toLocaleString() + " vues";
            },
          },
        },
      }
    );

    campaignChart.render();

    // CTR Chart
    let ctrData = stats.map((stat) => stat.click_rate ?? 0);
    let ctrLabels = stats.map((stat) => stat.task_name ?? "N/A");

    if (ctrData.length === 0) {
      ctrData = [0];
      ctrLabels = ["Aucune donnée"];
    }

    var ctrChart = new ApexCharts(document.querySelector("#ctr-chart"), {
      series: [
        {
          name: "Taux de clic",
          data: ctrData,
        },
      ],
      chart: {
        type: "bar",
        height: 320,
        toolbar: {
          show: false,
        },
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: "45%",
          endingShape: "rounded",
        },
      },
      dataLabels: {
        enabled: false,
      },
      colors: ["#45cb85"],
      xaxis: {
        categories: ctrLabels,
        labels: {
          show: true,
          trim: true,
          maxHeight: 50,
        },
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return val.toFixed(2) + "%";
          },
        },
      },
    });

    ctrChart.render();

    // Export functions - placeholder
    $("#exportCSV, #exportExcel, #exportPDF").on("click", function (e) {
      e.preventDefault();
      alert("Fonctionnalité d'export à implémenter");
    });
  });
}

if (window.location.pathname == "/admin/tasks") {
  $(document).ready(function () {
    // Initialize DataTable with proper configuration
    $("#tasks-datatable").DataTable({
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json",
      },
      order: [[0, "desc"]],
      columnDefs: [
        {
          orderable: false,
          targets: 6,
        }, // Disable sorting on actions column
      ],
      responsive: true,
      pageLength: 25,
    });

    // Approve task
    $(".approve-task").on("click", function () {
      const taskId = $(this).data("task-id");
      $("#approveTaskForm").attr("action", `/admin/task/${taskId}/approve`);
      $("#approveTaskModal").modal("show");
    });

    // Reject task
    $(".reject-task").on("click", function () {
      const taskId = $(this).data("task-id");
      $("#rejectTaskForm").attr("action", `/admin/task/${taskId}/reject`);
      $("#rejectTaskModal").modal("show");
    });

    // Delete task
    $(".delete-task").on("click", function () {
      const taskId = $(this).data("task-id");
      $("#deleteTaskForm").attr("action", `/admin/task/${taskId}/delete`);
      $("#deleteTaskModal").modal("show");
    });

    // Validate date range in filters
    $("#filtre_start_date, #filtre_end_date").on("change", function () {
      const startDate = $("#filtre_start_date").val();
      const endDate = $("#filtre_end_date").val();

      if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        alert("La date de début doit être antérieure à la date de fin.");
        $(this).val("");
      }
    });
  });
}

if (window.location.pathname.startsWith("/admin/task")) {
  $(document).ready(function () {
    // Initialize Select2 pour la vue principale
    $(".select2").select2({
      theme: "bootstrap-5",
      placeholder: "Sélectionnez une ou plusieurs options",
      allowClear: true,
    });

    // Initialiser Select2 spécifiquement pour le modal
    $("#editCampaignModal").on("shown.bs.modal", function () {
      $(".select2-modal").select2({
        theme: "bootstrap-5",
        placeholder: "Sélectionnez une ou plusieurs options",
        allowClear: true,
        dropdownParent: $("#editCampaignModal"),
      });
    });

    // Afficher/masquer le champ URL selon le type de média
    $('select[name="media_type"]').on("change", function () {
      var mediaType = $(this).val();

      // Gestion du champ URL
      if (mediaType === "image_link") {
        $(".url-field").show();
        $('input[name="url"]').attr("required", true);
        $(".url-required").show();
      } else {
        $(".url-field").hide();
        $('input[name="url"]').attr("required", false);
        $(".url-required").hide();
      }

      // Mise à jour du texte d'aide pour le média
      updateMediaTypeHint(mediaType, ".media-type-hint-modal");
    });

    // Fonction pour mettre à jour l'indication du type de média
    function updateMediaTypeHint(mediaType, selector) {
      if (mediaType === "image" || mediaType === "image_link") {
        $(selector).text("(Images uniquement, formats JPG, PNG, GIF)");
      } else if (mediaType === "video") {
        $(selector).text("(Vidéos uniquement, formats MP4, MOV)");
      } else if (mediaType === "text") {
        $(selector).text("(Aucun fichier nécessaire pour ce type)");
      } else {
        $(selector).text("(Images, vidéos selon le type de média choisi)");
      }
    }

    // La date de fin doit être >= date de début
    $('input[name="startdate"]').on("change", function () {
      const startDate = $(this).val();
      $('input[name="enddate"]').attr("min", startDate);

      if ($('input[name="enddate"]').val() < startDate) {
        $('input[name="enddate"]').val(startDate);
      }
    });

    // Gestion de la prévisualisation des fichiers
    $("#edit-campaign-files").on("change", function () {
      const fileInput = this;
      const previewContainer = $("#edit-file-preview");
      previewContainer.empty();

      if (fileInput.files && fileInput.files.length > 0) {
        // Créer la prévisualisation pour chaque fichier
        Array.from(fileInput.files).forEach(function (file, index) {
          // Créer l'élément d'aperçu
          const previewItem = $('<div class="col-md-3 mb-3"></div>');
          const card = $('<div class="card h-100"></div>');
          const cardBody = $('<div class="card-body p-2"></div>');

          // Si c'est une image, afficher une miniature
          if (file.type.match("image.*")) {
            const reader = new FileReader();
            reader.onload = function (e) {
              card.prepend(
                `<img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">`
              );
            };
            reader.readAsDataURL(file);
          } else if (file.type.match("video.*")) {
            // Pour les vidéos, afficher une icône
            card.prepend(
              '<div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 120px;"><i class="fa fa-film fa-2x text-white"></i></div>'
            );
          } else {
            // Pour les autres types de fichiers
            card.prepend(
              '<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;"><i class="fa fa-file fa-2x text-muted"></i></div>'
            );
          }

          // Ajouter le nom et la taille du fichier
          cardBody.append(
            `<p class="card-text small text-truncate mb-0">${file.name}</p>`
          );
          cardBody.append(
            `<p class="card-text small text-muted">${formatFileSize(
              file.size
            )}</p>`
          );

          card.append(cardBody);
          previewItem.append(card);
          previewContainer.append(previewItem);
        });
      }
    });

    // Fonction utilitaire pour formater la taille des fichiers
    function formatFileSize(bytes) {
      if (bytes === 0) return "0 Bytes";
      const k = 1024;
      const sizes = ["Bytes", "KB", "MB", "GB"];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    }

    // Validation du formulaire d'édition avant soumission
    $("#editCampaignForm").submit(function (e) {
      const mediaType = $(this).find('select[name="media_type"]').val();

      // Vérifier si un type de média a été sélectionné
      if (!mediaType) {
        e.preventDefault();
        alert("Veuillez sélectionner un type de média.");
        return false;
      }

      // Vérifier que l'URL est renseignée pour le type image_link
      if (mediaType === "image_link" && !$('input[name="url"]').val()) {
        e.preventDefault();
        alert("Veuillez renseigner une URL pour ce type de média.");
        return false;
      }

      // Si tout est valide
      return true;
    });
  });
}

if (window.location.pathname == "/admin/dashboard") {
  $(document).ready(function () {
    const taskStats = window.TASK_STATS;
    const userStats = window.USER_STATS;

    // -------------------------------
    // Task Status Chart
    // -------------------------------
    const taskCtx = document.getElementById("taskStatusChart").getContext("2d");

    const taskStatusChart = new Chart(taskCtx, {
      type: "bar",
      data: {
        labels: ["En attente", "Acceptées", "Payées", "Rejetées", "Fermées"],
        datasets: [
          {
            label: "Nombre de Campagnes",
            data: [
              taskStats.pending ?? 0,
              taskStats.accepted ?? 0,
              taskStats.paid ?? 0,
              taskStats.rejected ?? 0,
              taskStats.closed ?? 0,
            ],
            backgroundColor: [
              "rgba(255, 193, 7, 0.7)",
              "rgba(13, 110, 253, 0.7)",
              "rgba(25, 135, 84, 0.7)",
              "rgba(220, 53, 69, 0.7)",
              "rgba(108, 117, 125, 0.7)",
            ],
            borderColor: [
              "rgba(255, 193, 7, 1)",
              "rgba(13, 110, 253, 1)",
              "rgba(25, 135, 84, 1)",
              "rgba(220, 53, 69, 1)",
              "rgba(108, 117, 125, 1)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
            },
          },
        },
      },
    });

    // -------------------------------
    // User Distribution Chart
    // -------------------------------
    const userCtx = document
      .getElementById("userDistributionChart")
      .getContext("2d");

    const userDistributionChart = new Chart(userCtx, {
      type: "doughnut",
      data: {
        labels: ["Diffuseurs", "Annonceurs", "Administrateurs"],
        datasets: [
          {
            data: [
              userStats.influencers ?? 0,
              userStats.announcers ?? 0,
              userStats.admins ?? 0,
            ],
            backgroundColor: [
              "rgba(13, 110, 253, 0.7)",
              "rgba(25, 135, 84, 0.7)",
              "rgba(220, 53, 69, 0.7)",
            ],
            borderColor: [
              "rgba(13, 110, 253, 1)",
              "rgba(25, 135, 84, 1)",
              "rgba(220, 53, 69, 1)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        cutout: "60%",
      },
    });
  });
}

if (window.location.pathname == "/admin/client/dashboard") {
  $(document).ready(function () {
    const taskStats = window.taskStats;
    const paidBudget = window.paidBudget;
    const pendingBudget = window.pendingBudget;
    const remainingBudget = window.remainingBudget;
    // DataTable initialization
    $("#client-tasks-table").DataTable({
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
      },
    });

    // ApexCharts Budget Overview
    var options = {
      chart: {
        type: "bar",
        height: 320,
      },
      series: [
        {
          name: "Budget",
          data: [paidBudget, pendingBudget, remainingBudget],
        },
      ],
      xaxis: {
        categories: ["Payé", "En cours", "Restant"],
      },
      colors: ["#28a745", "#ffc107", "#0d6efd"],
    };
    var chart = new ApexCharts(
      document.querySelector("#budget-overview-chart"),
      options
    );
    chart.render();

    // ApexCharts Campaign Status
    var statusOptions = {
      chart: {
        type: "pie",
        height: 320,
      },
      series: [
        taskStats.pending ?? 0,
        taskStats.accepted ?? 0,
        taskStats.rejected ?? 0,
      ],
      labels: ["En attente", "Acceptées", "Rejetées"],
      colors: ["#ffc107", "#0d6efd", "#dc3545"],
    };
    var statusChart = new ApexCharts(
      document.querySelector("#campaign-status-chart"),
      statusOptions
    );
    statusChart.render();
  });
}

if (window.location.pathname == "/admin/users_diffuseur") {
  $(document).ready(function () {
    const countriesList = window.COUNTRIES_LIST;
    const localitiesList = window.LOCALITIES_LIST;

    // Initialize DataTable
    /* const dataTable = $("#items_datatable").DataTable({
      responsive: true,
      dom: "Bfrtip",
      buttons: ["copyHtml5", "excelHtml5", "csvHtml5", "pdfHtml5"],
      lengthMenu: [
        [10, 20, 50, -1],
        [10, 20, 50, "Tous"],
      ],
      pageLength: 20, // Affiche 20 données par page
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json",
      },
    }); */

    // Export buttons
    document
      .getElementById("export-excel")
      .addEventListener("click", function () {
        dataTable.button(".buttons-excel").trigger();
      });

    document
      .getElementById("export-pdf")
      .addEventListener("click", function () {
        dataTable.button(".buttons-pdf").trigger();
      });

    document
      .getElementById("export-csv")
      .addEventListener("click", function () {
        dataTable.button(".buttons-csv").trigger();
      });

    const countrySelect = document.getElementById("filtre_country");
    const localitySelect = document.getElementById("filtre_locality");

    const DEFAULT_LOCALITY_OPTION_HTML =
      '<option value="">Toutes les localités</option>';

    // === Fonction principale ===
    function populateLocalitiesForCountry(countryId, selectedLocality = "") {
      // Reset
      localitySelect.innerHTML = DEFAULT_LOCALITY_OPTION_HTML;

      // Trouver le pays sélectionné
      const country = countriesList.find(
        (c) => String(c.id) === String(countryId)
      );

      if (!country || country.name !== "Benin") {
        // Si ce n'est pas le Bénin → localités vides
        $("#filtre_locality").trigger("change.select2");
        return;
      }

      // Filtrer les localités du pays
      const filtered = localitiesList.filter(
        (l) => String(l.country_id) === String(countryId)
      );

      // Ajouter options
      filtered.forEach((loc) => {
        const option = document.createElement("option");
        option.value = loc.id;
        option.textContent = loc.name;

        if (String(loc.id) === String(selectedLocality)) {
          option.selected = true;
        }

        localitySelect.appendChild(option);
      });

      $("#filtre_locality").trigger("change.select2");
    }

    // === Listener du select pays ===
    if (countrySelect && localitySelect) {
      $("#filtre_country").on("change.select2", function () {
        const countryId = this.value;

        const selectedLocality = localitySelect.dataset.selected || "";
        populateLocalitiesForCountry(countryId, selectedLocality);
      });

      // Initialisation au chargement
      const initCountry = countrySelect.value || "";
      const selectedLocality = localitySelect.dataset.selected || "";

      if (initCountry !== "") {
        populateLocalitiesForCountry(initCountry, selectedLocality);
      }
    }

    // ---- Reset du formulaire (y compris Select2) ----
    const filterForm = document.querySelector("form.form.theme-form");
    if (filterForm) {
      filterForm.addEventListener("reset", function () {
        // timeout pour laisser le reset natif s'appliquer
        setTimeout(() => {
          // Select2 (si installé)
          if (typeof $ !== "undefined" && typeof $.fn.select2 !== "undefined") {
            $(".select2").val("").trigger("change");
            $(".select2-multiple").val([]).trigger("change");
          } else {
            // fallback vanilla : remettre à la valeur par défaut (premier option)
            document.querySelectorAll("select").forEach((s) => {
              if (s.multiple) {
                Array.from(s.options).forEach((opt) => (opt.selected = false));
              } else {
                s.selectedIndex = 0;
              }
            });
          }

          // remettre la liste des localités à l'état initial
          if (localitySelect)
            localitySelect.innerHTML = DEFAULT_LOCALITY_OPTION_HTML;
        }, 50);
      });
    }

    // Delete modal
    const deleteModal = document.getElementById("deleteModal");
    if (deleteModal) {
      deleteModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute("data-id");
        const name = button.getAttribute("data-name");

        document.getElementById("delete-user-name").textContent = name;
      });
    }

    // Initialize Select2 for dropdowns if available
    if (typeof $.fn.select2 !== "undefined") {
      $(".select2").select2({
        theme: "bootstrap-5",
      });
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    // Récupérer le jeton CSRF
    const csrfToken =
      document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") ||
      document.querySelector('input[name="_token"]')?.value;

    // 1. Gestion des boutons du dropdown
    /* document
      .querySelectorAll(".dropdown-menu a.dropdown-item")
      .forEach((item) => {
        // Pour les boutons Statistiques et Campagnes, rediriger vers la bonne URL
        if (
          item.innerHTML.includes("Statistiques") ||
          item.innerHTML.includes("Campagnes")
        ) {
          item.addEventListener("click", function (e) {
            e.preventDefault();

            // Récupérer l'ID de l'utilisateur
            const userId = this.getAttribute("data-user-id");

            // Déterminer l'action
            const action = this.innerHTML.includes("Statistiques")
              ? "stats"
              : "campaigns";

            // Rediriger vers l'URL avec l'action et l'ID
            const currentUrl = new URL(window.location.href);
            const baseUrl = currentUrl.pathname.split("?")[0];
            window.location.href = `${baseUrl}?action=${action}&id=${userId}`;
          });
        }
      }); */

    // 2. Gestion du changement de statut (toggle)
    document.querySelectorAll(".toggle-status").forEach((toggle) => {
      toggle.addEventListener("change", function () {
        const userId = this.getAttribute("data-user-id");
        const enabled = this.checked ? 1 : 0;
        const statusLabel =
          this.closest(".form-check").querySelector(".status-label") ||
          this.nextElementSibling;

        // Mise à jour visuelle immédiate
        if (statusLabel) {
          if (this.checked) {
            statusLabel.innerHTML = '<span class="text-success">Actif</span>';
          } else {
            statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
          }
        }

        // Envoyer la requête AJAX
        fetch(window.location.pathname, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            Accept: "application/json",
          },
          body: JSON.stringify({
            action: "toggle_status",
            user_id: userId,
            enabled: enabled,
          }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (!data.success) {
              // Réinitialiser le toggle en cas d'erreur
              this.checked = !this.checked;
              if (statusLabel) {
                if (this.checked) {
                  statusLabel.innerHTML =
                    '<span class="text-success">Actif</span>';
                } else {
                  statusLabel.innerHTML =
                    '<span class="text-danger">Inactif</span>';
                }
              }

              // Afficher l'erreur
              alert(data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);

            // Réinitialiser le toggle en cas d'erreur
            this.checked = !this.checked;
            if (statusLabel) {
              if (this.checked) {
                statusLabel.innerHTML =
                  '<span class="text-success">Actif</span>';
              } else {
                statusLabel.innerHTML =
                  '<span class="text-danger">Inactif</span>';
              }
            }

            alert("Une erreur est survenue lors de la modification du statut");
          });
      });
    });

    // 3. Configuration du modal de suppression
    const deleteModal = document.getElementById("deleteModal");
    if (deleteModal) {
      deleteModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;

        const userId = button.getAttribute("data-id");
        const userName = button.getAttribute("data-name");

        this.querySelector("#delete-user-name").textContent = userName;
        this.querySelector("#delete-user-id").value = userId;

        // Configurer l'action du formulaire
        const form = this.querySelector("form#delete-user-form");
        if (form) form.action = `${window.location.pathname}/delete/${userId}`;
      });
    }

    // 4. Interaction entre les filtres pays et localités
    const countrySelect = document.getElementById("filtre_country");
    const localitySelect = document.getElementById("filtre_locality");

    if (countrySelect && localitySelect) {
      countrySelect.addEventListener("change", function () {
        const selectedCountryId = this.value;

        // Vider la liste des localités
        localitySelect.innerHTML =
          '<option value="all">Toutes les localités</option>';

        if (selectedCountryId !== "all" && selectedCountryId !== "") {
          try {
            // Récupérer les données de localités depuis l'élément caché
            const localitiesJson = document.getElementById("localitiesJson");
            if (localitiesJson) {
              const localities = JSON.parse(localitiesJson.value);

              // Filtrer et ajouter les localités correspondant au pays sélectionné
              localities
                .filter((locality) => locality.country_id == selectedCountryId)
                .forEach((locality) => {
                  const option = document.createElement("option");
                  option.value = locality.id;
                  option.textContent = locality.name;
                  localitySelect.appendChild(option);
                });
            }
          } catch (error) {
            console.error("Erreur lors du chargement des localités:", error);
          }
        }
      });
    }
  });
}
