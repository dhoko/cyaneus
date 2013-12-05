(function (qwest){
    'use strict';

    var form = document.forms.cya_comments,
        coms = document.getElementById('commentcontainer'),
        tpl = document.getElementById('cyaTplComment');

    qwest.get(form.action+'?url')
        .success(function (rep){
            console.log(rep);

            var str = '';

            for (var i = 0; i< rep.length; i++) {
                var t = tpl.innerHTML;
                for(var key in rep[i]) {
                    t.replace(/{{key}}/g,rep[i][key]);
                }
                str += t;
            };
            coms.insertAdjacentHTML('beforeEnd',t);
        });

    form.url.value = window.location.pathname.replace('/','');

    form.send.onClick = function(){
        var data = {
            "about" : form.about.value,
            "info" : form.info.value,
            "url" : form.url.value,
            "name" : form.name.value,
            "mail" : form.mail.value,
            "content" : form.content.value,
        };

        qwest.post(form.action,data)
            .success(function (rep){
                console.log(rep);

                var t = tpl.innerHtml;

                for(var key in data) {
                    t.replace(/{{key}}/g,data[key]);
                }

                coms.insertAdjacentHTML('beforeEnd',t);

            })
            .error(function (rep){
                alert('Cannot post your comment :/');
            });
    };
})(qwest);