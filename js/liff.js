function initializeLiff(myLiffId) {
    liff
        .init({
            liffId: myLiffId,
            withLoginOnExternalBrowser: true, // Enable automatic login process
        })
        .then(() => {
            initializeApp();
        })
        .catch((err) => {
            console.log(err);
            console.log('啟動失敗。');
        });
}

function initializeApp() {
    console.log('啟動成功。');
    liff.getProfile()
        .then(profile => {
            console.log(profile.displayName);
            console.log(profile.userId);
            console.log(profile.statusMessage);
            console.log(profile);

            sessionStorage.setItem('lineUserId',profile.userId);
            const lineId = $("#lineId").val(sessionStorage.getItem('lineUserId'));
        })
        .catch((err) => {
            console.log('error', err);
        });


    // // get access token
    // if (!liff.isLoggedIn() && !liff.isInClient()) {
    //     window.alert('To get an access token, you need to be logged in. Tap the "login" button below and try again.');
    // } else {
    //     const accessToken = liff.getAccessToken();
    //     console.log(accessToken);
    // }


}

//使用 LIFF_ID 初始化 LIFF 應用
initializeLiff('2000183731-BLmrAGPp');