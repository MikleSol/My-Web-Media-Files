/* Demo Note:  This demo uses a FileProgress class that handles the UI for displaying the file name and percent complete.
The FileProgress class is not part of SWFUpload.
*/


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */

this.stoped=0;

function fileQueued(file) {
	try {
		this.customSettings.tdFilesQueued.innerHTML = this.getStats().files_queued;
	} catch (ex) {
		this.debug(ex);
	}

}

function fileDialogComplete() {
	this.startUpload();
}

function uploadStart(file) {
	$("#dialog-modal").dialog('open');
	try {
		this.customSettings.progressCount = 0;
		updateDisplay.call(this, file);
	}
	catch (ex) {
		this.debug(ex);
	}
	
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		this.customSettings.progressCount++;
		updateDisplay.call(this, file);
	} catch (ex) {
		this.debug(ex);
	}
}

function pausecomp(millis) 
{
var date = new Date();
var curDate = null;

do { curDate = new Date(); } 
while(curDate-date < millis);
} 

function uploadSuccess(file, serverData) {
	try {
		updateDisplay.call(this, file);
	} catch (ex) {
		this.debug(ex);
	}
		if (serverData.substring(0, 7) === "FILEID:") {
			this.file_id=serverData.substring(7);
		} else {
			this.errorup=1
			alert(serverData);
			$("#dialog-modal").dialog('close');
		}
}

function uploadComplete(file) {
	if(this.errorup != 1){
		$("#dialog-modal").dialog('close');
		pausecomp(1000);
		tab_add("#view_"+this.file_id,"Информация о файле","/ajax_view?file="+this.file_id);
	}
}

function add_tab(file_id) 
{ 
  $("#upload_box").tabs("add" , "#view_"+file_id , "Информация о файле" , 1 );
	$("#upload_box").tabs("select","#view_"+file_id);
	$("#view_"+file_id).load('/ajax_view?file='+file_id);
}



function updateDisplay(file) {
	$("#progressbar").progressbar({ 
		value: file.percentUploaded,
	});
	this.customSettings.tdCurrentSpeed.innerHTML = SWFUpload.speed.formatBPS(file.currentSpeed);
	this.customSettings.tdTimeRemaining.innerHTML = SWFUpload.speed.formatTime(file.timeRemaining);
	this.customSettings.tdTimeElapsed.innerHTML = SWFUpload.speed.formatTime(file.timeElapsed);
	this.customSettings.tdPercentUploaded.innerHTML = SWFUpload.speed.formatPercent(file.percentUploaded);
	this.customSettings.tdSizeUploaded.innerHTML = SWFUpload.speed.formatBytes(file.sizeUploaded);
}
