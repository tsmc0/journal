
    $$.getElementById('auth_sumbit').onclick = () => {
        if (!checkVals($$.getElementById('username').value) || !checkVals($$.getElementById('userpass').value)){
            showToast('ERROR', 'Не все поля заполнены');
        } else {
            doPost([
                ['act', 'auth'],
                ['username', $$.getElementById('username').value],
                ['userpass', $$.getElementById('userpass').value]
            ], function (data){
                if (data.status == 200){
                    localStorage.setItem('_sess', data.content.sess);
                    localStorage.setItem('userData', JSON.stringify(data.content.userData));

                    navTo('home');
                } else {
                    showToast('ERROR', data.content);
                }
            });
        }
    }

    $$.getElementById('nav_to_reg').onclick = () => {
        navTo('reg');
    }