let nextBtn = document.querySelector('.next');
let prevBtn = document.querySelector('.prev');

nextBtn.addEventListener('click', function() {
    let items = document.querySelectorAll('.slides');
    document.querySelector('.slider').appendChild(items[0]);
});

prevBtn.addEventListener('click', function() {
    let items = document.querySelectorAll('.slides');
    document.querySelector('.slider').prepend(items[items.length - 1]);
});
