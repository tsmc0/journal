
    function doPost(datagramArray, callback){
        let formData = new FormData();

        datagramArray.forEach(e => {
            formData.append(e[0], e[1]);
        });
        
        $.ajax({
            url: 'api/api.php',
            method: 'POST',
            async: false,
            data: formData,
            processData: false,
            responeType: 'json',
            contentType: false,
            success: function (data){
                callback(JSON.parse(data));

                console.log(data);
            },
            error: function (data, se) {

            }
        })
    }