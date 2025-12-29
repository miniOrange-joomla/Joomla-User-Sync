
function back_btn(){
    
    jQuery("#f").submit();
}


function mo_show_tab(tab_id)
{
    jQuery(".mo_boot_sync-tab").css("background",'none');
    jQuery(".mo_boot_sync-tab").css("color",'white');
    jQuery(".mo_sync_tab").css('display','none');
    jQuery("#"+tab_id).css('display','block');
    jQuery("#mo_"+tab_id).css("background",'white');
    jQuery("#mo_"+tab_id).css("color",'black');
    
}

function mo_test_configuration(){
		
    var username = jQuery("#mo_usersync_upn").val();
    var appname = jQuery("#moAppName").val();

    if(username){
        testconfigurl ='index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moGetClient&username='+btoa(username)+'&appName='+btoa(appname);
        var myWindow = window.open(testconfigurl, 'TEST ATTRIBUTE MAPPING', 'scrollbars=1 width=800, height=800');
    }else{
        alert("Please enter username to see what attributes are retrieved by entered username");
    }
    var timer = setInterval(function() {   
        if(myWindow.closed) {  
            clearInterval(timer);  
            location.reload();
        }  
    }, 1); 
}