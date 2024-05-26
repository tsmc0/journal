
    let $$ = document;

    function navTo(addr){
        window.location.replace(addr);
    }

    function checkVals(val){
        if (val.trim() == '' || val.Lenght == 0){
            return false;
        } else {
            return true;
        }
    }

    let toast = null;
    let toastTimer;

    function showToast(type, data){
        if (toast == null){
            let h1 = $$.createElement('h3');
            h1.textContent = data;

            let wrap = document.createElement('div');
            wrap.classList.add('toast');
            wrap.classList.add(type);
            wrap.appendChild(h1);

            $$.body.appendChild(wrap);
            toast = wrap;

            toastTimer = setTimeout(() => {hideToast()}, 5000);
        }
    }

    function hideToast(){
        destroyEl(toast);
        clearTimeout(toastTimer);
    }

    function destroyEl(el){
        el.parentNode.removeChild(el);
    }

    function timeFormat(unix, time = true){
        var a = new Date(unix * 1000);
        var months = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
        var year = a.getFullYear();
        var month = months[a.getMonth()];
        var date = a.getDate();
        var hour = a.getHours();
        var min = a.getMinutes();
        var sec = a.getSeconds();
        return date + ' ' + month + ' ' + year + ' ' + hour + ':' + min;
    }

    function getFirstDay(d) {
        d = new Date(d);
        var day = d.getDay(),
            diff = d.getDate() - day + (day == 0 ? -6 : 1);
        fr = new Date(d.setDate(diff));

        return [fr.getDay(), fr.getMonth()];
    }

    function getDayFromUnix(time){
        let d  = new Date(time*1000);

        return d.getDay()+1
    }

    function getMonthFromUnix(time){
        let d  = new Date(time*1000);

        return d.getMonth()+1
    }

    function currentDay(){
        d = new Date();
        return d.getDay() + 1;
    }

    function toUnixTime(day, month, year){
        let date = new Date(Date.UTC(year, month - 1, day));
        return Math.floor(date.getTime()/1000);
    }


