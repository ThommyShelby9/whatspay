
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
  var bjIdField = document.getElementById("bjId")
  if(bjIdField !== undefined && bjIdField !== null) {
    bjId = $('#bjId').val();
  }

  var country = document.getElementById("country");
  if (country !== undefined && country !== null) {
    $('#country').on('change', function (event) {
      event.preventDefault();
      var content = '';
      if($('#country').val() === bjId){
        $.each(localities, function(i, item) {
          content += '<option value="'+item.id+'">'+item.name+'</option>';
        });
      }else{
        $.each(countries, function(i, item) {
          if( $('#country').val() === item.id) {
            content = '<option value="">' + item.name + '</option>';
          }
        });
      }
      $('#locality').html(content)
    });
  }

  var login_submit_button = document.getElementById("login_submit_button");
  if (login_submit_button !== undefined && login_submit_button !== null) {
    $("#login_submit_button").click(function () {
        $('#login_form').submit();
    });
  }

  var registration_submit_button = document.getElementById("registration_submit_button");
  if (registration_submit_button !== undefined && registration_submit_button !== null) {
    $("#registration_submit_button").click(function () {
      $('#registration_form').submit();
    });
  }

  var forgottenpassword_submit_button = document.getElementById("forgottenpassword_submit_button");
  if (forgottenpassword_submit_button !== undefined && forgottenpassword_submit_button !== null) {
    $("#forgottenpassword_submit_button").click(function () {
      $('#forgottenpassword_form').submit();
    });
  }

  var task_submit_Button = document.getElementById("task_submit_Button");
  if (task_submit_Button !== undefined && task_submit_Button !== null) {
    $("#task_submit_Button").click(function () {
      $('#taskfiles').val(JSON.stringify(uploadedFiles));
      $('#task_form').submit();
    });
  }

  var whatsappNumberGenerateCodeButton = document.getElementById("whatsappNumberGenerateCodeButton");
  if (whatsappNumberGenerateCodeButton !== undefined && whatsappNumberGenerateCodeButton !== null) {
    $("#whatsappNumberGenerateCodeButton").click(function () {
      let valid = true;
      let fields = [
        {'field': "phone", 'message': "Veuillez bien saisir le numero de telephone"},
      ]
      $.each(fields, function(i, item) {
        if($('#'+item.field).val().replace(/ /g , "") === ""){
          valid = false;
          $.notify(item.message, "error");
          $('#'+item.field).focus()
        }
      })
      if(valid === true){

        var json = '';
        json = JSON.stringify({
          "session":$('#session').val(),
          "phonecountry":$('#phonecountry').val(),
          "phone":$('#phone').val(),
        });
        var url = $('#baseUrl').val()+'/api/whatsapp/generatecode';
        var xhr = new XMLHttpRequest();
        xhr.open("POST", ''+url)
        xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
        $('#whatsappNumberGenerateCodeButton').attr('disabled', 'disabled');
        $('#phonecountry').attr('disabled', 'disabled');
        $('#phone').attr('disabled', 'disabled');
        $.notify("Traitement en cours", "warn");
        xhr.send(json)
        xhr.onload = function() {};
        xhr.onerror = function() {};
        xhr.onprogress = function(event) {};
        xhr.onreadystatechange = function() {
          if (xhr.readyState !== 4) return false;
          if (xhr.status !== 200) {
            //console.log(xhr.response);
            try {
              var response = JSON.parse(xhr.response);
              if(response.error_message !== undefined){
                $.notify(response.error_message, "error");
              }else{
                $.notify("Une erreur est survenue", "error");
              }
            }catch(err) {
              $.notify("Une erreur est survenue", "error");
            }
            $('#whatsappNumberGenerateCodeButton').removeAttr('disabled')
            $('#phonecountry').removeAttr('disabled');
            $('#phone').removeAttr('disabled');
            return false;
          } else {
            console.log(xhr.response);
            var response = JSON.parse(xhr.response);
            if(response.error === false){
              $.notify(response.error_message, "success");
              //$('#whatsappNumberGenerateCodeButton').removeAttr('disabled')
              $('#codeValidationDiv').attr('style', '');
              $('#whatsappNumberId').val(response.operator_response.pId);
            }else{
              $.notify(response.error_message, "error")
              $('#whatsappNumberGenerateCodeButton').removeAttr('disabled');
              $('#phonecountry').removeAttr('disabled');
              $('#phone').removeAttr('disabled');
            }
          }
        }

      }
    });
  }

  var whatsappNumberValidateCodeButton = document.getElementById("whatsappNumberValidateCodeButton");
  if (whatsappNumberValidateCodeButton !== undefined && whatsappNumberValidateCodeButton !== null) {
    $("#whatsappNumberValidateCodeButton").click(function () {
      let valid = true;
      let fields = [
        {'field': "phone", 'message': "Veuillez bien saisir le numero de telephone"},
        {'field': "code", 'message': "Veuillez bien saisir le code de validation"},
      ]
      $.each(fields, function(i, item) {
        if($('#'+item.field).val().replace(/ /g , "") === ""){
          valid = false;
          $.notify(item.message, "error");
          $('#'+item.field).focus()
        }
      })
      if(valid === true){

        var json = '';
        json = JSON.stringify({
          "session":$('#session').val(),
          "phonecountry":$('#phonecountry').val(),
          "phone":$('#phone').val(),
          "code":$('#code').val(),
          "whatsappNumberId":$('#whatsappNumberId').val(),
        });
        var url = $('#baseUrl').val()+'/api/whatsapp/validatecode';
        var xhr = new XMLHttpRequest();
        xhr.open("POST", ''+url)
        xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
        $('#whatsappNumberGenerateCodeButton').attr('disabled', 'disabled');
        $('#phonecountry').attr('disabled', 'disabled');
        $('#phone').attr('disabled', 'disabled');
        $('#whatsappNumberValidateCodeButton').attr('disabled', 'disabled');
        $('#code').attr('disabled', 'disabled');
        $.notify("Traitement en cours", "warn");
        xhr.send(json)
        xhr.onload = function() {};
        xhr.onerror = function() {};
        xhr.onprogress = function(event) {};
        xhr.onreadystatechange = function() {
          if (xhr.readyState !== 4) return false;
          if (xhr.status !== 200) {
            //console.log(xhr.response);
            try {
              var response = JSON.parse(xhr.response);
              if(response.error_message !== undefined){
                $.notify(response.error_message, "error");
              }else{
                $.notify("Une erreur est survenue", "error");
              }
            }catch(err) {
              $.notify("Une erreur est survenue", "error");
            }
            $('#whatsappNumberGenerateCodeButton').removeAttr('disabled')
            $('#phonecountry').removeAttr('disabled');
            $('#phone').removeAttr('disabled');
            $('#whatsappNumberValidateCodeButton').removeAttr('disabled');
            $('#code').removeAttr('disabled');
            return false;
          } else {
            console.log(xhr.response);
            var response = JSON.parse(xhr.response);
            if(response.error === false){
              $.notify(response.error_message, "success");
              //console.log($('#baseUrl').val()+'/admin/whatsappnumbers')
              //$('#whatsappNumberGenerateCodeButton').removeAttr('disabled')
              var timeleft = 2;
              var downloadTimer = setInterval(function(){
                if(timeleft <= 0){
                  clearInterval(downloadTimer);
                  document.location.href = $('#baseUrl').val()+'/admin/whatsappnumbers';
                  return false;
                }
                timeleft -= 1;
              }, 1000);

            }else{
              $.notify(response.error_message, "error")
              $('#whatsappNumberGenerateCodeButton').removeAttr('disabled');
              $('#phonecountry').removeAttr('disabled');
              $('#phone').removeAttr('disabled');
              $('#whatsappNumberValidateCodeButton').removeAttr('disabled');
              $('#code').removeAttr('disabled');
            }
          }
        }

      }
    });
  }

  var categoriesDiv = document.getElementById("categoriesDiv");
  var profil = document.getElementById("profil");
  if (categoriesDiv !== undefined && categoriesDiv !== null && profil !== undefined && profil !== null) {
    $('#profil').on('change', function (event) {
      event.preventDefault();
      if($('#profil').val() === "DIFFUSEUR"){
        $('#categoriesDiv').attr('style', '');
        $('#vuesmoyenDiv').attr('style', '');
        $('#langDiv').attr('style', '');
        $('#contenttypeDiv').attr('style', '');
        $('#studyDiv').attr('style', '');
        $('#occupationDiv').attr('style', '');
      }else{
        $('#categoriesDiv').attr('style', 'display:none');
        $('#vuesmoyenDiv').attr('style', 'display:none');
        $('#langDiv').attr('style', 'display:none');
        $('#contenttypeDiv').attr('style', 'display:none');
        $('#studyDiv').attr('style', 'display:none');
        $('#occupationDiv').attr('style', 'display:none');
      }
    });
    if($('#linkprofile').val() === "DIFFUSEUR"){
      $('#categoriesDiv').attr('style', '');
      $('#vuesmoyenDiv').attr('style', '');
      $('#langDiv').attr('style', '');
      $('#contenttypeDiv').attr('style', '');
      $('#studyDiv').attr('style', '');
      $('#occupationDiv').attr('style', '');
    }else{
      $('#categoriesDiv').attr('style', 'display:none');
      $('#vuesmoyenDiv').attr('style', 'display:none');
      $('#langDiv').attr('style', 'display:none');
      $('#contenttypeDiv').attr('style', 'display:none');
      $('#studyDiv').attr('style', 'display:none');
      $('#occupationDiv').attr('style', 'display:none');
    }
    $('#occupation').on('change', function (event) {
      event.preventDefault();
      if($('#occupation').val() === ""){
        $('#autre_occupationDiv').attr('style', 'margin-top: 10px;');
      }else{
        $('#autre_occupationDiv').attr('style', 'margin-top: 10px; display:none');
      }
    });
  }

  var dropZoneDiv = document.getElementById("dropZoneDiv");
  if (dropZoneDiv !== undefined && dropZoneDiv !== null){
    createUploadForm('filesForm', 'dropZoneDiv');
  }

  var filtre_country = document.getElementById("filtre_country");
  var filtre_locality = document.getElementById("filtre_locality");
  if (filtre_country !== undefined && filtre_country !== null) {
    $('#filtre_country').on('change', function (event) {
      event.preventDefault();
      var content = '<option value="all">Toutes les localites</option>';
      if($('#filtre_country').val() === bjId){
        $.each(localities, function(i, item) {
          content += '<option value="'+item.id+'">'+item.name+'</option>';
        });
      }
      if(filtre_locality !== undefined && filtre_locality !== null) {
        $('#filtre_locality').html(content)
      }
    });
  }


  var filtre_category = document.getElementById("filtre_category");
  if (filtre_category !== undefined && filtre_category !== null) {
    $('#filtre_category').select2({
      theme: "bootstrap-5",
      width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
      placeholder: $(this).data('placeholder'),
      closeOnSelect: false,
    });
  }

  var filtre_contenu = document.getElementById("filtre_contenu");
  if (filtre_contenu !== undefined && filtre_contenu !== null) {
    $('#filtre_contenu').select2({
      theme: "bootstrap-5",
      width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
      placeholder: $(this).data('placeholder'),
      closeOnSelect: false,
    });
  }

  var filtre_study = document.getElementById("filtre_study");
  if (filtre_study !== undefined && filtre_study !== null) {
    $('#filtre_study').select2({
      theme: "bootstrap-5",
      width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
      placeholder: $(this).data('placeholder'),
      closeOnSelect: false,
    });
  }


  var filtre_occupation = document.getElementById("filtre_occupation");
  if (filtre_occupation !== undefined && filtre_occupation !== null) {
    $('#filtre_occupation').select2({
      theme: "bootstrap-5",
      width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
      placeholder: $(this).data('placeholder'),
      closeOnSelect: false,
    });
  }

  var filtre_lang = document.getElementById("filtre_lang");
  if (filtre_lang !== undefined && filtre_lang !== null) {
    $('#filtre_lang').select2({
      theme: "bootstrap-5",
      width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
      placeholder: $(this).data('placeholder'),
      closeOnSelect: false,
    });
  }


  var items_datatable = document.getElementById("items_datatable");
  if(items_datatable !== undefined && items_datatable !== null){
    $("#items_datatable").DataTable({
      dom: "Bfrtip",
      buttons: ["copyHtml5", "excelHtml5", "csvHtml5", "pdfHtml5"]
    });
  }

});

//https://apalfrey.github.io/select2-bootstrap-5-theme/examples/multiple-select/

function showToast(message, type){
  $.notify(message, type); //error ; success ; warn ; info
}

function displayToast(type, message){
  let uuid = crypto.randomUUID();
  var html = '<div class="toast hide toast fade" id="'+uuid+'" role="alert" aria-live="assertive" aria-atomic="true">\n' +
    '                        <div class="d-flex justify-content-between alert-'+type+'">\n' +
    '                          <div class="toast-body">'+message+'</div>\n' +
    '                          <button class="btn-close btn-close-white me-2 m-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>\n' +
    '                        </div>\n' +
    '                      </div>'
  $('#toastDiv').append(html)
  const content = document.getElementById(uuid);
  const toast = new bootstrap.Toast(content);
  toast.show();
}

function numerique(event) {
  var key = window.event ? event.keyCode : event.which;
  if (event.keyCode === 8 || event.keyCode === 46
    || event.keyCode === 37 || event.keyCode === 39) {
    return true;
  }
  else if ( key < 48 || key > 57 ) {
    return false;
  }
  else {
    return true;
  }
}

function numeriqueNumberOnly(event) {
  var key = window.event ? event.keyCode : event.which;
  if ( key < 48 || key > 57 ) {
    return false;
  } else {
    return true;
  }
}

function isEmail(email) {
  var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}

function acceptUpload(file, done) {
  done();
}

function finalizeUpload(response, type){
  if(response.error === false) {
    $.each(response.files, function(i, item) {
      switch (parseInt(type)) {
        case 1:
          uploadedFiles.push(item)
          break;
      }
    });
  }
  console.log(JSON.stringify(uploadedFiles));
}

function createUploadForm(id, uploadRow, acceptedFiles = "image/*,application/pdf,.psd,.zip,.mp3,.m4a,.m4b,.m4p,.docs,.docx,.xls,.xlsx", type = 1) {
  var uploadForm = '<form class="dropzone dropzone-primary" id="'+id+'" action="'+$('#baseUrl').val()+'/api/upload"><div class="dz-message needsclick"><i class="icon-cloud-up"></i>' +
    '<h6><span data-i18n="drop_file"></span></h6><span class="note needsclick"><span data-i18n="drop_file_note"></span></span></div></form>';
  document.getElementById(uploadRow).innerHTML = uploadForm;
  new Dropzone("#"+id, {
    url: $('#baseUrl').val()+'/api/upload',
    paramName: "files",
    maxFiles: 10,
    maxFilesize: 10,
    acceptedFiles: acceptedFiles,
    dictDefaultMessage: "Déposer des fichiers ici pour les télécharger",
    dictFallbackMessage: "Votre navigateur ne prend pas en charge les téléchargements de fichiers par glisser-déposer.",
    dictFileTooBig: "Fichier volumineux ({{filesize}}MiB). Taille maximale: {{maxFilesize}}MiB.",
    dictInvalidFileType: "Vous ne pouvez pas télécharger ce type de fichiers.",
    dictResponseError: "Le serveur a répondu avec le code {{statusCode}} .",
    dictCancelUpload: "Annuler téléchargement",
    dictUploadCanceled: "Téléchargement annulé.",
    dictCancelUploadConfirmation: "Voulez-vous vraiment annuler ce téléchargement ?",
    dictRemoveFile: "Supprimer le fichier",
    dictMaxFilesExceeded: "Vous ne pouvez plus télécharger de fichiers.",
    accept: function(file, done) {
      acceptUpload(file, done);
    },
    init: function() {
      this.on("success", function(file, response) {
        console.log(response);
        finalizeUpload(response, type)
      });
      this.on("addedfile", function(file) { console.log("Added file."); });
      this.on("error", function(file, errorMessage) { console.log(errorMessage); });
      this.on("processing", function(file) { console.log("processing"); });
      this.on("uploadprogress", function(file) { console.log("uploadprogress"); });
      this.on("sending", function(file) { console.log("sending."); });
      this.on("success", function(file, response) { console.log(response); });
      this.on("complete   ", function(file) { console.log("complete "); });
      this.on("canceled", function(file) { console.log("canceled"); });
      this.on("successmultiple", function(file) { console.log("successmultiple"); });

    }
  });
}


var currentTab = 0;
var wizardtypevalue = ""
var wizardtype = document.getElementById("wizardtype");
if (wizardtype !== undefined && wizardtype !== null) {
  wizardtypevalue = $('#wizardtype').val();
  if( wizardtypevalue === "registration"){
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
  return typeof value === 'number';
}
function validateForm() {
  var valid = true;

  switch (wizardtypevalue){
    case "registration":
      switch (currentTab){
        case 0:
          let fields = [
            {'field': "prenom", 'message': "Veuillez bien indiquer votre prenom"},
            {'field': "nom", 'message': "Veuillez bien indiquer votre nom"},
            {'field': "phone", 'message': "Veuillez bien indiquer votre contact"},
            {'field': "email", 'message': "Veuillez bien indiquer votre adresse mail"},
            {'field': "password", 'message': "Veuillez bien indiquer votre mot de passe"},
            {'field': "password_confirmation", 'message': "Veuillez bien confirmer votre mot de passe"}
          ]
          $.each(fields, function(i, item) {
            if($('#'+item.field).val().replace(/ /g , "") === ""){
              valid = false;
              $.notify(item.message, "error");
              $('#'+item.field).focus()
            }
          })
          if(valid === true){
            if(($('#email').val().replace(/ /g , "") !== "") && !isEmail($('#email').val().replace(/ /g , ""))){
              valid = false;
              $.notify("Email incorrect", "error");
              $('#email').focus()
            }else if( $('#password').val() !== $('#password_confirmation').val() ){
              valid = false;
              $.notify("Les mots de passe ne correspondent pas", "error");
              $('#password_confirmation').focus()
            }
          }
          break;
        case 1:
          let fields2 = [
            {'field': "profil", 'message': "Veuillez bien indiquer votre profil"},
          ]
          $.each(fields2, function(i, item) {
            if($('#'+item.field).val().replace(/ /g , "") === ""){
              valid = false;
              $.notify(item.message, "error");
              $('#'+item.field).focus()
            }
          })
          if(valid === true){
            let fields3 = [
              {'field': "vuesmoyen", 'message': "Veuillez bien indiquer votre nombre de vues moyen"}
            ]
            $.each(fields3, function(i, item) {
              if($('#'+item.field).val().replace(/ /g , "") === ""){
                valid = false;
                $.notify(item.message, "error");
                $('#'+item.field).focus()
              }
            })
            if($('#profil').val() === "DIFFUSEUR"){
              if($('#occupation').val() === ""){
                let fields3 = [
                  {'field': "autre_occupation", 'message': "Veuillez bien saisir votre profession"},
                ]
                $.each(fields3, function(i, item) {
                  if($('#'+item.field).val().replace(/ /g , "") === ""){
                    valid = false;
                    $.notify(item.message, "error");
                    $('#'+item.field).focus()
                  }
                })
              }
              var nbC = 0;
              var nbCt = 0;
              $.each(categories, function(i, item) {
                if(document.getElementById("c_"+item.id).checked === true){
                  nbC = nbC +1
                }
              });
              $.each(contenttypes, function(i, item) {
                if(document.getElementById("ct_"+item.id).checked === true){
                  nbCt = nbCt +1
                }
              });

              if(nbC === 0){
                valid = false;
                $.notify("Veuillez bien indiquer au moins une categorie de publication", "error");
              }

              if(nbCt === 0){
                valid = false;
                $.notify("Veuillez bien indiquer au moins un type de contenu", "error");
              }
            }
          }

          break;
        case 2:
          if(document.getElementById("termes").checked !== true){
            valid = false;
            $.notify("Veuillez bien accepter les termes et conditions", "error");
            $('#birthday').focus()
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
