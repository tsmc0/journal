
    $$.getElementById('reg_sumbit').onclick = () => {
        if (!checkVals($$.getElementById('username').value) || !checkVals($$.getElementById('userpass').value) || !checkVals($$.getElementById('name').value)|| !checkVals($$.getElementById('lastName').value)){
            showToast('ERROR', 'Не все поля заполнены');
        } else {
            doPost([
                ['act', 'reg'],
                ['username', $$.getElementById('username').value],
                ['name', $$.getElementById('name').value],
                ['lastName', $$.getElementById('lastName').value],
                ['fatherName', $$.getElementById('fatherName').value],
                ['userpass', $$.getElementById('userpass').value],
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

    $$.getElementById('nav_to').onclick = () => {
        navTo('login');
    }