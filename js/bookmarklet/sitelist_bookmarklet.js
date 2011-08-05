function GetMetaValue(meta_name){
    var my_arr = document.getElementsByTagName('META');
    for (var counter = 0; counter < my_arr.length; counter++) {
        if (my_arr[counter].name.toLowerCase() == meta_name.toLowerCase()) {
            return my_arr[counter].content;
        }
    }
    return '';
}

function sitelist(){
    var destination = 'http://sitelist.in/add/';
    var sitelist_url = '';
    sitelist_url = document.location.protocol.toString() + '//' + document.location.hostname.toString();
    sitelist_url = sitelist_url.replace(/^\s|\s$/g, '');
    sitelist_url = encodeURIComponent(sitelist_url);
    
    var sitelist_title = document.title;
    sitelist_title = encodeURIComponent(sitelist_title);
    
    var sitelist_desc = GetMetaValue('description');
    sitelist_desc = encodeURIComponent(sitelist_desc);
    
    window.open(destination + '?source=bookmarklet&' + 'bookmarklet_url=' + sitelist_url + '&bookmarklet_title=' + sitelist_title + '&bookmarklet_desc=' + sitelist_desc);
}

sitelist();