document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.translate-btn').forEach(function (btn) {

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const from = document.getElementById(this.dataset.from);
            const to   = document.getElementById(this.dataset.to);

            if (!from || !to) return;

            const text = from.value.trim();
            if (!text) {
                alert('يرجى إدخال نص عربي أولاً');
                return;
            }

            fetch(
                'https://translate.googleapis.com/translate_a/single' +
                '?client=gtx&sl=ar&tl=en&dt=t&q=' + encodeURIComponent(text)
            )
            .then(res => res.json())
            .then(data => {
                let translated = '';
                data[0].forEach(item => translated += item[0]);
                to.value = translated;
            })
            .catch(() => {
                alert('حدث خطأ أثناء الترجمة');
            });
        });

    });

});
