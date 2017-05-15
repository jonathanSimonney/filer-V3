
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

    for (var i in buttonReplace){
        if (typeof buttonReplace[i].style != 'undefined') {
            buttonReplace[i].onclick = function(){
                var arrayElements = this.parentNode.childNodes;
                for (var i in arrayElements){
                    if (typeof arrayElements[i].style != 'undefined') {
                        if (arrayElements[i].className.indexOf("replaceForm") != -1) {
                            var toShowForm = arrayElements[i];
                            break;
                        }
                    }
                }

                toggleFormState(toShowForm);
            }
        }
    }

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

                toggleFormState(toShowForm);
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

                toggleFormState(toShowForm);
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