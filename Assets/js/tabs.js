//========================================TABS========================================//

//====================GLOBAL VARIABLES====================//
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {
    $(document).on("click", ".tablinks", function (e) {
        openTab(e, $(this).attr("id"));
        this.className += " activeTab";
    });
});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function openTab(evt, selectedTab) {
    var tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    var tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace("activeTab", "");
    }
    document.getElementById(selectedTab + "Content").style.display = "block";
}
//====================FUNCTIONS====================//

//========================================TABS========================================//