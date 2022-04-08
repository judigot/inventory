//========================================MODULE/APP NAME========================================//

//====================GLOBAL VARIABLES====================//
var key = {
  a: 65,
  b: 66,
  c: 67,
  d: 68,
  e: 69,
  f: 70,
  g: 71,
  h: 72,
  i: 73,
  j: 74,
  k: 75,
  l: 76,
  m: 77,
  n: 78,
  o: 79,
  p: 80,
  q: 81,
  r: 82,
  s: 83,
  t: 84,
  u: 85,
  v: 86,
  w: 87,
  x: 88,
  y: 89,
  z: 90,
};
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {
  $("html").focus();
  $("body").attr("spellcheck", false);
  $(document).on("contextmenu", function (e) {
    return false;
  });
  $(document).on("keyup", "input", function (e) {
    if (custom_isEmptyInput(this.value)) {
      this.value = "";
    }
  });
});
//==========BOOTSTRAP TOOLTIP==========//
$(function () {
  $(document).ready(function () {
    $("[data-toggle='tooltip']").tooltip();
  });
});
//==========BOOTSTRAP CONFIRMATION==========//
$(function () {
  $("[data-toggle=confirmation]").confirmation({
    rootSelector: "[data-toggle=confirmation]",
  });
});
//==========BOOTSTRAP CONFIRMATION==========//

//====================GENERAL====================//

//====================MODULE FUNCTIONALITY====================//
$(function () {});
//====================MODULE FUNCTIONALITY====================//

//====================FUNCTIONS====================//
function helloWorld() {
  alert("Hello, world!");
}

function get(data, url) {
  return $.ajax({
    url: url,
    type: "GET",
    dataType: "json",
    data: data,
  });
}

function post(data, url) {
  return $.ajax({
    url: url,
    type: "POST",
    dataType: "json",
    data: data,
  });
}

function custom_quickTable(tableIdentifier, data, tfoot) {
  var columnNames = Object.keys(data[0]);
  let theadHTML = (tbodyHTML = tfootHTML = "");
  var tableElements = {
    table: tableIdentifier + "-tbody-qt tbody-qt",
    th: tableIdentifier + "-th-qt th-qt",
    tr: tableIdentifier + "-tr-qt tr-qt",
    td: tableIdentifier + "-td-qt td-qt",
    tfoot: tableIdentifier + "-tfoot-qt tfoot-qt",
  };
  for (var i = 0; i < columnNames.length; i++) {
    theadHTML += `<th class='${i}-${tableElements["th"]}'>${columnNames[i]}</th>`;
  }
  for (var i = 0; i < data.length; i++) {
    tbodyHTML += `<tr class='${tableElements["tr"]}'>`;
    for (var j = 0; j < columnNames.length; j++) {
      var rowData = data[i][columnNames[j]] ? data[i][columnNames[j]] : "-";
      tbodyHTML += `<td class='${tableElements["td"]}'>${rowData}</td>`;
    }
    tbodyHTML += "</tr>";
  }
  if (tfoot) {
    tfootHTML += `<tfoot><tr class='${tableElements["tr"]}'>`;
    for (var i = 0; i < tfoot.length; i++) {
      tfootHTML += `<td class='${tableElements["td"]}'>${tfoot[i]}</td>`;
    }
    tfootHTML += "</tr></tfoot>";
  }
  return `<table id='${tableIdentifier}' class='${tableElements["table"]}'>\n\
            <thead><tr class='${tableElements["tr"]}'>${theadHTML}</tr></thead>\n\
            <tbody>${tbodyHTML}</tbody>${tfootHTML}\
            </table>`;
}

function custom_notify(content, position) {
  if ($(".notifyjs-wrapper").is(":visible")) {
    $(".notifyjs-wrapper").remove();
  }
  $.notify(content, {
    className: "danger",
    position: position === null ? "bottom left" : position,
  });
}

function custom_capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

function custom_monetize(value) {
  return value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function custom_isEmptyInput(string) {
  return !string.replace(/\s/g, "").length ? true : false;
}

function custom_sanitizeInput(string) {
  return string.replace(/\s/g, "");
}

function custom_playAudio(audioName, audioSource) {
  var selector =
    audioName[0] === "#"
      ? 'id="' + audioName.substr(1) + '"'
      : 'class="' + audioName.substr(1) + '"';
  if ($(audioName).length === 0) {
    $("body").append(
      "<audio " +
        selector +
        ' preload="auto"><source src="' +
        audioSource +
        '"></audio>'
    );
  }
  var audio = $(document).find(audioName)[0].play();
  if (audio !== undefined) {
    audio.then((_) => {}).catch((error) => {});
  }
}
//====================FUNCTIONS====================//

//========================================MODULE/APP NAME========================================//
