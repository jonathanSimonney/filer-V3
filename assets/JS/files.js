
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

    $.each($('.file'), function (index, value) {
        linkFileEvent(value.id);
    });

    $.each($('.folder'), function (index, value) {
        linkFolderEvent(value.id);
    });

    //D&D handler

    buttonFolder.onclick = function () {
        toggleFormState(document.querySelector('.addFolder'));
    };

    buttonUpload.onclick = function(){
        toggleFormState(document.querySelector('.uploadForm'));
    };

    function FileConvertSize(aSize){
        aSize = Math.abs(parseInt(aSize, 10));
        var def = [[1, 'octets'], [1024, 'ko'], [1024*1024, 'Mo'], [1024*1024*1024, 'Go'], [1024*1024*1024*1024, 'To']];
        for(var i=0; i<def.length; i++){
            if(aSize<def[i][0]) return (aSize/def[i-1][0]).toFixed(2)+' '+def[i-1][1];
        }
    }

    $('.fileSize').each(function () {
        var fileSize = $(this).html();
        var convertedFileSize = FileConvertSize(fileSize);
        $(this).html(convertedFileSize);
    });



    $('.replace').click(function() {
        if($(this).siblings('.replaceForm').hasClass("collapsed")){
            $(this).siblings('.replaceForm').removeClass('collapsed');
            $(this).siblings().find('.label_choose_file').html("<p>choose a file</p>");
            $(this).siblings().find('.label_confirm').html("<i class='fa fa-check'></i><p>confirm</p>");
            $(this).html("<i class='fa fa-times'></i>");
            $(this).addClass('close_options');
        }
        else{
            $(this).siblings('.replaceForm').addClass('collapsed');
            $(this).siblings().find('.label_choose_file').html("");
            $(this).siblings().find('.label_confirm').html("");
            $(this).html("<i class='fa fa-exchange'></i>");
            $(this).removeClass('close_options');
        }
    });


    $('.delete').click(function() {
        $('.modal-body').html('Are you sure you want to delete this file?');
    });


    $('#fileFormReplace').change(function (){
        $(this).parents('.fileFormReplace').submit();
    });

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

    $('.show').click(function (e) {
        e.preventDefault();

    })
};