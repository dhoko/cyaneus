(function (qwest){
    'use strict';

    var form = document.forms.cya_comments,
        coms = document.getElementById('commentcontainer'),
        tpl  = document.getElementById('cyaTplComment');

    qwest.get(form.action+'?url='+btoa(window.location.pathname))
        .success(function (rep){
            console.log('Get your comments');

            var str = '';

            for (var i = 0; i< rep.length; i++) {
                var t = tpl.innerHTML;
                for(var key in rep[i]) {

                    t = t.replace(new RegExp('{{'+key+'}}','g'),rep[i][key]);
                  }
                str += t;
            };
            coms.insertAdjacentHTML('beforeEnd',str);
        });

    form.pathurl.value = btoa(window.location.pathname);

    form.addEventListener('submit', function(e){

        e.preventDefault();
        e.stopPropagation();
        var data = {
            "about"   : form.about.value,
            "info"    : form.info.value,
            "pathurl" : form.pathurl.value,
            "url"     : form.url.value,
            "name"    : form.name.value,
            "mail"    : form.mail.value,
            "content" : form.content.value,
        };

        qwest.post(form.action,data)
            .success(function (rep){
                console.log('Post your comment');

                var t = tpl.innerHTML;
                data.hash = rep.hashmail;


                for(var key in data) {
                    t = t.replace(new RegExp('{{'+key+'}}','g'),data[key]);
                }

                coms.insertAdjacentHTML('beforeEnd',t);
                form.reset();
            })
            .error(function (rep){
                console.error('Cannot post your comment')
                alert('Cannot post your comment :/');
            });
    });
})(qwest);
