function initializeLiff(myLiffId) {
    liff
        .init({
            liffId: myLiffId,
            withLoginOnExternalBrowser: true, // Enable automatic login process
        })
        .then(() => {
            initializeApp();
            alert(liff.getLanguage());
        })
        .catch((err) => {
            alert('啟動失敗。');
        });
}

function initializeApp() {
    var h = document.getElementById('result');
    h.innerHTML = 'Hello!!';
    liff.getProfile()
        .then(profile => {
            alert(profile.displayName);
            alert(profile.userId);
            alert(profile.statusMessage);
            alert(profile);
            sessionStorage.setItem('lineUserId', profile.userId);

        })
        .catch((err) => {
            console.log('error', err);
            alert('error=' + err);
        });

}

//使用 LIFF_ID 初始化 LIFF 應用
initializeLiff('2000183731-BLmrAGPp');