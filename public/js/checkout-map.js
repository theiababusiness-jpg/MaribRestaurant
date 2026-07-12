(function () {
    const c = window.checkoutConfig;

    if (!c || typeof L === "undefined") {
        return;
    }

    const lang = document.documentElement.lang || "ar";

    const el = {
        branch: document.getElementById("branchSelect"),
        branchName: document.getElementById("branchNamePreview"),
        branchAddress: document.getElementById("branchAddressPreview"),
        branchLink: document.getElementById("branchMapLink"),
        status: document.getElementById("mapStatus"),
        map: document.getElementById("googleMap"),
        search: document.getElementById("searchQuery"),
        btnSearch: document.getElementById("btnSearch"),
        btnMyLocation: document.getElementById("btnMyLocation"),
        lat: document.getElementById("latInput"),
        lng: document.getElementById("lngInput"),
        mapAddress: document.getElementById("mapAddressInput"),
        mapAddressEditor: document.getElementById("mapAddressEditor"),
        metaAddress: document.getElementById("metaAddress"),
        metaCoordinates: document.getElementById("metaCoordinates"),
        metaDistance: document.getElementById("metaDistance"),
        metaDeliveryFee: document.getElementById("metaDeliveryFee"),
        metaDeliveryState: document.getElementById("metaDeliveryState"),
        deliveryFee: document.getElementById("deliveryFeeValue"),
        total: document.getElementById("grandTotalValue"),
        submit: document.getElementById("checkoutSubmit"),
        submitHelp: document.getElementById("submitHelp"),
    };

    const state = {
        map: null,
        baseLayer: null,
        labelsLayer: null,
        branchMarker: null,
        customerMarker: null,
        branch: null,
        customer: null,
        quote: null,
        ready: false,
        requestId: 0,
        submitting: false,
    };

    const money = (n) => `${Number(n).toFixed(2)} ${c.texts.currency}`;
    const km = (n) => `${Number(n).toFixed(2)} ${c.texts.km}`;
    const method = () => document.querySelector('input[name="fulfillment_method"]:checked')?.value || "delivery";
    const findBranch = (id) => c.branches.find((item) => String(item.id) === String(id)) || null;
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');

    function readCookie(name) {
        const prefix = `${name}=`;
        const row = document.cookie
            .split(";")
            .map((item) => item.trim())
            .find((item) => item.startsWith(prefix));

        if (!row) {
            return "";
        }

        return decodeURIComponent(row.slice(prefix.length));
    }

    function currentCsrfToken() {
        return readCookie("XSRF-TOKEN") || csrfMeta?.content || c.csrfToken || "";
    }

    function applyCsrfToken(token) {
        const input = document.querySelector('#checkoutForm input[name="_token"]');

        if (input && token) {
            input.value = token;
        }

        if (csrfMeta && token) {
            csrfMeta.content = token;
        }

        c.csrfToken = token;
        return token;
    }

    function syncCsrfToken() {
        return applyCsrfToken(currentCsrfToken());
    }

    async function refreshCsrfToken() {
        if (!c.refreshCsrfUrl) {
            return syncCsrfToken();
        }

        try {
            const response = await fetch(c.refreshCsrfUrl, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
                cache: "no-store",
            });

            if (!response.ok) {
                throw new Error("CSRF_REFRESH_FAILED");
            }

            const payload = await response.json().catch(() => ({}));
            return applyCsrfToken(payload?.token || currentCsrfToken());
        } catch (error) {
            return syncCsrfToken();
        }
    }

    function setStatus(message, tone) {
        el.status.textContent = message;
        el.status.classList.remove("is-success", "is-error");

        if (tone) {
            el.status.classList.add(`is-${tone}`);
        }
    }

    function fillBranch(branch) {
        el.branchName.textContent = branch?.name || c.texts.na;
        el.branchAddress.textContent = branch?.address || c.texts.na;

        if (el.branchLink) {
            if (branch?.google_maps_url) {
                el.branchLink.href = branch.google_maps_url;
                el.branchLink.classList.remove("checkout-hidden");
            } else {
                el.branchLink.href = "#";
                el.branchLink.classList.add("checkout-hidden");
            }
        }
    }

    function fillLocation(address) {
        const resolvedAddress = address || c.texts.na;

        if (el.mapAddressEditor) {
            el.mapAddressEditor.value = address || "";
        }

        if (el.metaAddress) {
            el.metaAddress.textContent = resolvedAddress;
        }

        if (el.metaCoordinates) {
            el.metaCoordinates.textContent = state.customer
                ? `${state.customer.lat.toFixed(6)}, ${state.customer.lng.toFixed(6)}`
                : c.texts.na;
        }
    }

    function syncTotals() {
        const fee = method() === "delivery" && state.quote ? Number(state.quote.delivery_fee) : 0;
        el.deliveryFee.textContent = money(fee);
        el.total.textContent = money(c.itemsSubtotal + fee);
        el.metaDeliveryFee.textContent = money(fee || c.deliveryFee);
    }

    function syncSubmit() {
        let reason = "";

        if (!c.checkoutReady) {
            reason = c.texts.checkout_not_ready;
        } else if (!state.branch) {
            reason = c.texts.select_branch;
        } else if (method() === "pickup") {
            if (!state.branch.pickup_enabled) {
                reason = c.texts.pickup_disabled;
            }
        } else if (state.branch.lat === null || state.branch.lng === null) {
            reason = c.texts.branch_coords_missing;
        } else if (!state.branch.delivery_enabled) {
            reason = c.texts.delivery_disabled;
        } else if (!state.ready) {
            reason = c.texts.map_load_failed;
        } else if (!state.customer) {
            reason = c.texts.location_required;
        } else if (!state.quote) {
            reason = c.texts.delivery_bad;
        }

        el.submit.disabled = reason !== "";
        el.submitHelp.textContent = reason || c.texts.submit_ready;
        syncTotals();
    }

    function syncRadios() {
        const delivery = document.getElementById("fulfillmentDelivery");
        const pickup = document.getElementById("fulfillmentPickup");
        const allowDelivery = Boolean(state.branch?.delivery_enabled);
        const allowPickup = Boolean(state.branch?.pickup_enabled);

        delivery.disabled = !allowDelivery;
        pickup.disabled = !allowPickup;

        if (delivery.disabled && delivery.checked && allowPickup) {
            pickup.checked = true;
        }

        if (pickup.disabled && pickup.checked && allowDelivery) {
            delivery.checked = true;
        }
    }

    async function reverseLookup(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=${encodeURIComponent(lang)}&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`;
        const response = await fetch(url, {
            headers: {
                Accept: "application/json",
            },
        });

        if (!response.ok) {
            throw new Error("REVERSE_FAILED");
        }

        const row = await response.json();
        return row?.display_name || `${lat}, ${lng}`;
    }

    async function searchAddress(query) {
        const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&accept-language=${encodeURIComponent(lang)}&q=${encodeURIComponent(query)}`;
        const response = await fetch(url, {
            headers: {
                Accept: "application/json",
            },
        });

        if (!response.ok) {
            throw new Error("SEARCH_FAILED");
        }

        const rows = await response.json();

        if (!Array.isArray(rows) || !rows.length) {
            throw new Error("SEARCH_FAILED");
        }

        return {
            lat: Number(rows[0].lat),
            lng: Number(rows[0].lon),
            address: rows[0].display_name || query,
        };
    }

    async function requestQuote(csrfToken) {
        return fetch(c.quoteUrl, {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                branch_id: state.branch.id,
                lat: state.customer.lat,
                lng: state.customer.lng,
            }),
        });
    }

    async function fetchQuote() {
        const id = ++state.requestId;
        let response = await requestQuote(syncCsrfToken());
        let payload = await response.json().catch(() => ({}));

        if (response.status === 419) {
            response = await requestQuote(await refreshCsrfToken());
            payload = await response.json().catch(() => ({}));
        }

        if (id !== state.requestId) {
            return null;
        }

        if (!response.ok) {
            throw new Error(payload.message || c.texts.delivery_bad);
        }

        return payload.data;
    }

    async function resolveQuote() {
        if (method() !== "delivery" || !state.customer || !state.branch) {
            state.quote = null;
            el.metaDistance.textContent = c.texts.na;
            el.metaDeliveryState.textContent = method() === "pickup" ? c.texts.pickup_hint : c.texts.delivery_rule;
            syncSubmit();
            return;
        }

        try {
            const quote = await fetchQuote();

            if (!quote) {
                return;
            }

            state.quote = quote;
            el.metaDistance.textContent = km(quote.distance_km);
            el.metaDeliveryState.textContent = c.texts.delivery_ok;
            setStatus(`${c.texts.delivery_ok} ${km(quote.distance_km)} - ${money(quote.delivery_fee)}`, "success");
        } catch (error) {
            state.quote = null;
            el.metaDistance.textContent = c.texts.na;
            el.metaDeliveryState.textContent = c.texts.delivery_bad;
            setStatus(error.message || c.texts.delivery_bad, "error");
        }

        syncSubmit();
    }

    async function setCustomer(lat, lng, options = {}) {
        if (!state.ready || !state.map) {
            setStatus(c.texts.map_load_failed, "error");
            syncSubmit();
            return;
        }

        state.customer = {
            lat: Number(lat),
            lng: Number(lng),
        };

        el.lat.value = state.customer.lat;
        el.lng.value = state.customer.lng;

        if (!state.customerMarker) {
            state.customerMarker = L.marker([state.customer.lat, state.customer.lng], {
                draggable: true,
            });

            state.customerMarker.on("dragend", async function (event) {
                const point = event.target.getLatLng();
                await setCustomer(point.lat, point.lng);
            });
        }

        state.customerMarker.setLatLng([state.customer.lat, state.customer.lng]);

        if (!state.map.hasLayer(state.customerMarker)) {
            state.customerMarker.addTo(state.map);
        }

        if (options.recenter) {
            state.map.setView([state.customer.lat, state.customer.lng], Math.max(state.map.getZoom(), 16));
        }

        setStatus(c.texts.location_pending);

        let address = options.address || "";

        if (!address) {
            try {
                address = await reverseLookup(state.customer.lat, state.customer.lng);
            } catch (error) {
                address = `${state.customer.lat.toFixed(6)}, ${state.customer.lng.toFixed(6)}`;
            }
        }

        el.mapAddress.value = address;
        fillLocation(address);

        if (method() === "pickup") {
            state.quote = null;
            el.metaDistance.textContent = c.texts.na;
            el.metaDeliveryState.textContent = c.texts.pickup_hint;
            setStatus(c.texts.pickup_hint);
            syncSubmit();
            return;
        }

        await resolveQuote();
    }

    function setBranch(branchId) {
        state.branch = findBranch(branchId);
        state.quote = null;
        fillBranch(state.branch);
        syncRadios();

        if (!state.branch) {
            if (state.branchMarker && state.map?.hasLayer(state.branchMarker)) {
                state.map.removeLayer(state.branchMarker);
            }

            setStatus(c.texts.select_branch, "error");
            syncSubmit();
            return;
        }

        if (state.branch.lat === null || state.branch.lng === null) {
            if (state.branchMarker && state.map?.hasLayer(state.branchMarker)) {
                state.map.removeLayer(state.branchMarker);
            }

            el.metaDistance.textContent = c.texts.na;
            el.metaDeliveryState.textContent = c.texts.branch_coords_missing;
            setStatus(c.texts.branch_coords_missing, "error");
            syncSubmit();
            return;
        }

        if (!state.branchMarker) {
            state.branchMarker = L.marker([state.branch.lat, state.branch.lng]);
        } else {
            state.branchMarker.setLatLng([state.branch.lat, state.branch.lng]);
        }

        if (!state.map.hasLayer(state.branchMarker)) {
            state.branchMarker.addTo(state.map);
        }

        state.map.setView([state.branch.lat, state.branch.lng], 17);

        if (method() === "pickup") {
            el.metaDistance.textContent = c.texts.na;
            el.metaDeliveryState.textContent = c.texts.pickup_hint;
            setStatus(c.texts.pickup_hint);
        } else if (state.customer) {
            void resolveQuote();
        } else {
            el.metaDistance.textContent = c.texts.na;
            el.metaDeliveryState.textContent = c.texts.delivery_rule;
            setStatus(c.texts.branch_ready);
        }

        syncSubmit();
    }

    async function onSearchClick() {
        const query = (el.search.value || "").trim();

        if (!query) {
            setStatus(c.texts.search_empty, "error");
            return;
        }

        try {
            const result = await searchAddress(query);
            await setCustomer(result.lat, result.lng, {
                address: result.address,
                recenter: true,
            });
        } catch (error) {
            setStatus(c.texts.search_failed, "error");
        }
    }

    function onMyLocationClick() {
        if (!navigator.geolocation) {
            setStatus(c.texts.my_location_failed, "error");
            return;
        }

        navigator.geolocation.getCurrentPosition(
            async function (pos) {
                await setCustomer(pos.coords.latitude, pos.coords.longitude, {
                    recenter: true,
                });
            },
            function () {
                setStatus(c.texts.my_location_failed, "error");
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function initMap() {
        const first = c.branches.find((item) => item.lat !== null && item.lng !== null);
        const center = first ? [Number(first.lat), Number(first.lng)] : [24.7136, 46.6753];
        const zoom = first ? 16 : 6;

        state.map = L.map(el.map).setView(center, zoom);

        state.baseLayer = L.tileLayer("https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", {
            maxZoom: 19,
            attribution: "Tiles &copy; Esri",
        }).addTo(state.map);

        state.labelsLayer = L.tileLayer("https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}", {
            maxZoom: 19,
            attribution: "Labels &copy; Esri",
            pane: "overlayPane",
        }).addTo(state.map);

        state.map.on("click", async function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            await setCustomer(lat, lng);
        });

        state.ready = true;
        setBranch(el.branch.value || c.initialBranchId);

        if (c.oldLocation.lat && c.oldLocation.lng) {
            void setCustomer(Number(c.oldLocation.lat), Number(c.oldLocation.lng), {
                address: c.oldLocation.mapAddress || "",
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        syncCsrfToken();

        el.branch.addEventListener("change", function () {
            setBranch(el.branch.value);
        });

        document.querySelectorAll('input[name="fulfillment_method"]').forEach(function (item) {
            item.addEventListener("change", function () {
                if (method() === "pickup") {
                    state.quote = null;
                    el.metaDistance.textContent = c.texts.na;
                    el.metaDeliveryState.textContent = c.texts.pickup_hint;
                    setStatus(c.texts.pickup_hint);
                    syncSubmit();
                    return;
                }

                if (state.customer) {
                    void resolveQuote();
                } else {
                    setStatus(state.branch ? c.texts.branch_ready : c.texts.select_branch);
                    syncSubmit();
                }
            });
        });

        el.btnSearch.addEventListener("click", onSearchClick);
        el.search.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                onSearchClick();
            }
        });
        el.btnMyLocation.addEventListener("click", onMyLocationClick);
        if (el.mapAddressEditor) {
            el.mapAddressEditor.addEventListener("input", function () {
                const value = (el.mapAddressEditor.value || "").trim();
                el.mapAddress.value = value;
                if (el.metaAddress) {
                    el.metaAddress.textContent = value || c.texts.na;
                }
            });
        }

        fillBranch(findBranch(el.branch.value || c.initialBranchId));
        fillLocation(el.mapAddress.value || "");

        const checkoutForm = document.getElementById("checkoutForm");
        checkoutForm?.addEventListener("submit", async function (event) {
            if (state.submitting) {
                return;
            }

            event.preventDefault();
            state.submitting = true;

            if (el.submit) {
                el.submit.disabled = true;
            }

            try {
                await refreshCsrfToken();
                checkoutForm.submit();
            } catch (error) {
                state.submitting = false;
                if (el.submit) {
                    el.submit.disabled = false;
                }
                setStatus(c.texts.map_load_failed, "error");
            }
        });

        window.addEventListener("pageshow", function () {
            void refreshCsrfToken();
        });

        try {
            initMap();
        } catch (error) {
            setStatus(c.texts.map_load_failed, "error");
        }

        syncSubmit();
    });
})();
