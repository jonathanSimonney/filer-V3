
var openedFileId = 0;

window.onload = function(){
    var buttonReplace = document.querySelectorAll('.replace');
    var buttonRename = document.querySelectorAll('.rename');
    var buttonDelete = document.querySelectorAll('.delete');
    var buttonUpload = document.getElementById('upload');
    var buttonFolder = document.getElementById('folder');
    var buttonShowing = document.querySelectorAll('.show');

    //D&D handler

    var fileName = document.querySelectorAll('.name');
    var folder = document.querySelectorAll('.folder');
    var precedent = document.querySelector('.precedent');

    for (var i in fileName){
        if (fileName[i].style !== undefined){
            fileName[i].draggable = true;
            fileName[i].addEventListener('dragstart', function (e) {
                dragFileOrFolder(this, e);
            })
        }
    }

    for (var i in folder){
        if (folder[i].style !== undefined){
            linkFolderDragAndDropEvent(folder[i]);
        }
    }

    if (precedent !== null){
        linkArrowDragAndDropEvent(precedent);
    }

    //D&D handler

    buttonFolder.onclick = function () {
        toggleFormState(document.querySelector('.addFolder'));
    };

    buttonUpload.onclick = function(){
        toggleFormState(document.querySelector('.uploadForm'));
    };

    $('.rename').click(function() {
        if($(this).siblings('.renameForm').hasClass("collapsed")){
            $(this).siblings('.renameForm').removeClass('collapsed');
            $(this).siblings().find('.rename_input').focus();
            $(this).siblings().find('label').html("<i class='fa fa-check'></i><p>confirm</p>");
            $(this).html("<i class='fa fa-times'></i>");
        }
        else{
            $(this).siblings('.renameForm').addClass('collapsed');
            $(this).siblings().find('label').html("");
            $(this).html("<i class='fa fa-pencil'></i>");
        }
    });

    $('.replace').click(function() {
        if($(this).siblings('.replaceForm').hasClass("collapsed")){
            $(this).siblings('.replaceForm').removeClass('collapsed');
            $(this).siblings().find('.label_choose_file').html("<p>choose a file</p>");
            $(this).siblings().find('.label_confirm').html("<i class='fa fa-check'></i><p>confirm</p>");
            $(this).html("<i class='fa fa-times'></i>");
        }
        else{
            $(this).siblings('.replaceForm').addClass('collapsed');
            $(this).siblings().find('.label_choose_file').html("");
            $(this).siblings().find('.label_confirm').html("");
            $(this).html("<i class='fa fa-exchange'></i>");
        }
    });


    /*$('#fileFormReplace').change(function (){
        $(this).parents('.fileFormReplace').submit();
    });*/

    for (var i in buttonRename){
        if (typeof buttonRename[i].style != 'undefined') {
            buttonRename[i].onclick = function(){
                var arrayElements = this.parentNode.childNodes;
                for (var i in arrayElements){
                    if (typeof arrayElements[i].style != 'undefined') {
                        if (arrayElements[i].className.indexOf("renameForm") != -1) {
                            var toShowForm = arrayElements[i];
                            break;
                        }
                    }
                }

                /*toggleFormState(toShowForm);*/
            }
        }
    }

    for (var i in buttonDelete){
        if (typeof buttonDelete[i].style != 'undefined') {
            buttonDelete[i].onclick = function(){
                var arrayElements = this.parentNode.childNodes;
                for (var i in arrayElements){
                    if (typeof arrayElements[i].style != 'undefined') {
                        if (arrayElements[i].className.indexOf("deleteForm") != -1) {
                            var toShowForm = arrayElements[i];
                            break;
                        }
                    }
                }

                /*toggleFormState(toShowForm);*/
            }
        }
    }

    for (var i in buttonShowing){
        if (typeof buttonShowing[i].style !== 'undefined'){
            buttonShowing[i].onclick = function (e) {
                e.preventDefault();
                showInFullScreen(this.parentNode);
            }
        }
    }
}