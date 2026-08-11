async function registerAndSubscribe(onSubscribe: (subs: PushSubscription | null) => void,
                                           onError: (e: Error) => void): Promise<void> {
    try {
        await navigator.serviceWorker.register('./sw.js');
        //subscribe to notification
        navigator.serviceWorker.ready
            .then((registration: ServiceWorkerRegistration) => {
                return registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: "BNpe0M-28BH0N1vupdLEKwomJeeTW5HJKwXrN-iEqULLEzMdGnTUX-vmwITtp4ae2iE_6vDXnPKcEGYqiCnbzBQ",
                });
            })
            .then((subscription: PushSubscription) => {
                console.info("Created subscription Object: ", subscription.toJSON());
                onSubscribe(subscription);
            })
            .catch((e) => {
                onError(e);
            });
    } catch (e: any) {
        onError(e);
    }
}
registerAndSubscribe()