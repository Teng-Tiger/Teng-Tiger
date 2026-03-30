// 移动端菜单
document.querySelector('.menu-toggle')?.addEventListener('click', () => {
    document.querySelector('nav').classList.toggle('active');
});

// 滚动按钮
const scrollTop = document.getElementById('scrollTop');
const scrollBottom = document.getElementById('scrollBottom');
if (scrollTop) {
    scrollTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    scrollBottom.addEventListener('click', () => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }));
}

// 爆款区横向滚动箭头
const scrollLeft = document.getElementById('scrollLeft');
const scrollRight = document.getElementById('scrollRight');
const featuredScroll = document.getElementById('featuredScroll');
if (scrollLeft && featuredScroll) {
    scrollLeft.onclick = () => featuredScroll.scrollBy({ left: -300, behavior: 'smooth' });
    scrollRight.onclick = () => featuredScroll.scrollBy({ left: 300, behavior: 'smooth' });
}
const annScrollLeft = document.getElementById('annScrollLeft');
const annScrollRight = document.getElementById('annScrollRight');
const annScroll = document.getElementById('annScroll');
if (annScrollLeft && annScroll) {
    annScrollLeft.onclick = () => annScroll.scrollBy({ left: -300, behavior: 'smooth' });
    annScrollRight.onclick = () => annScroll.scrollBy({ left: 300, behavior: 'smooth' });
}

// 价格区间筛选表单自动提交
const priceSelect = document.querySelector('select[name="price_range"]');
if (priceSelect) {
    priceSelect.addEventListener('change', () => {
        const form = priceSelect.closest('form');
        if (form) form.submit();
    });
}

// 购物车数量输入框限制
document.querySelectorAll('input[name^="quantity"]').forEach(input => {
    input.addEventListener('change', () => {
        let val = parseInt(input.value);
        if (isNaN(val) || val < 1) input.value = 1;
    });
});

// 收藏按钮异步切换（可选，如果使用 AJAX）
const favBtn = document.getElementById('favBtn');
if (favBtn) {
    favBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.href;
        fetch(url, { method: 'GET' })
            .then(() => location.reload())
            .catch(err => console.error(err));
    });
}