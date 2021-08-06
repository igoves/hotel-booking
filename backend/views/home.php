<?php
add_thickbox();


?>
<style>

    /*body {*/
    /*    background: #fff;*/
    /*}*/
    /*.nav-tab-active, .nav-tab-active:focus, .nav-tab-active:focus:active, .nav-tab-active:hover {*/
    /*    background:#fff !important;*/
    /*    border-bottom: 1px solid #fff;*/
    /*}*/

    .vue-input-tag-wrapper .new-tag {
        margin-bottom: 0;
        padding: 0;
    }

    [v-cloak] {
        display: none;
    }
</style>

<div id="app">
    <h2 class="nav-tab-wrapper">
        <router-link class="nav-tab" exact-active-class="nav-tab-active" to="/"><span
                class="dashicons dashicons-calendar-alt"></span> Dashboard
        </router-link>
        <router-link class="nav-tab" active-class="nav-tab-active" to="/orders"><span
                class="dashicons dashicons-cart"></span> Orders
        </router-link>
        <router-link class="nav-tab" active-class="nav-tab-active" to="/room_types"><span
                class="dashicons dashicons-tag"></span> Room Types
        </router-link>
        <router-link class="nav-tab" active-class="nav-tab-active" to="/rooms"><span
                class="dashicons dashicons-building"></span> Rooms
        </router-link>
        <router-link class="nav-tab" active-class="nav-tab-active" to="/settings"><span
                class="dashicons dashicons-admin-settings"></span> Settings
        </router-link>
    </h2>
    <div v-cloak style="padding-right:10px;">
        <router-view></router-view>
    </div>
</div>

<script>
    "use strict";

    const store = new Vuex.Store({
        state: {

        },
        getters: {

        },
        mutations: {

        },
        actions: {

        },
    });

    const Dashboard = {

        mounted() {

            scheduler.locale.labels.section_fio = 'Name';
            scheduler.locale.labels.section_email = 'Email';
            scheduler.locale.labels.section_tel = 'Phone';
            scheduler.locale.labels.section_room = 'Room';
            scheduler.locale.labels.section_noty = 'Noty';
            scheduler.locale.labels.section_status = 'Status';
            scheduler.locale.labels.section_is_paid = 'Paid';
            scheduler.locale.labels.section_time = 'Date';
            scheduler.xy.scale_height = 30;
            scheduler.config.details_on_create = true;
            scheduler.config.details_on_dblclick = true;
            scheduler.config.prevent_cache = true;
            scheduler.config.show_loading = true;
            scheduler.config.xml_date = "%Y-%m-%d %H:%i";

            let roomsArr = scheduler.serverList("room");
            let roomTypesArr = scheduler.serverList("roomType");
            let roomStatusesArr = scheduler.serverList("roomStatus");
            let bookingStatusesArr = scheduler.serverList("bookingStatus");

            scheduler.config.lightbox.sections = [
                {map_to: "fio", name: "fio", type: "textarea", height: 30},
                {map_to: "email", name: "email", type: "textarea", height: 30},
                {map_to: "tel", name: "tel", type: "textarea", height: 30},
                {map_to: "noty", name: "noty", type: "textarea", height: 30},
                {map_to: "room", name: "room", type: "select", options: scheduler.serverList("currentRooms")},
                {map_to: "status", name: "status", type: "radio", options: scheduler.serverList("bookingStatus")},
                {map_to: "is_paid", name: "is_paid", type: "checkbox", checked_value: true, unchecked_value: false},
                {map_to: "time", name: "time", type: "calendar_time"}
            ];

            scheduler.locale.labels.timeline_tab = 'Timeline';

            scheduler.createTimelineView({
                fit_events: true,
                name: "timeline",
                y_property: "room",
                render: 'tree',
                x_unit: "day",
                x_date: "%d",
                x_step: 1,
                x_size: 31,
                // x_size: 2,
                // x_start: 0,
                // x_length: 2,
                dy: 32,
                event_dy: 'full',
                section_autoheight: false,
                round_position: true,
                scrollable: true,
                y_unit: scheduler.serverList("currentRooms"),
                second_scale: {
                    x_unit: "month",
                    x_date: "%F"
                }
            });

            function findInArray(array, key) {
                for (var i = 0; i < array.length; i++) {
                    if (key == array[i].key)
                        return array[i];
                }
                return null;
            }

            function getRoomType(key) {
                return findInArray(roomTypesArr, key).label;
            }

            function getRoomStatus(key) {
                return findInArray(roomStatusesArr, key);
            }

            function getRoom(key) {
                return findInArray(roomsArr, key);
            }

            scheduler.templates.timeline_scale_label = function (key, label, section) {

                var roomStatus = getRoomStatus(section.status);
                return ["<div class='timeline_item_separator'></div>",
                    "<div class='timeline_item_cell'>" + label + "</div>",
                    "<div class='timeline_item_separator'></div>",
                    "<div class='timeline_item_cell'>" + getRoomType(section.type) + "</div>",
                    "<div class='timeline_item_separator'></div>",
                    "<div class='timeline_item_cell room_status'>",
                    "<span class='room_status_indicator room_status_indicator_" + roomStatus.key + "'></span>",
                    "<span class='status-label'>" + roomStatus.label + "</span>",
                    "</div>"].join("");
            };

            scheduler.attachEvent("onBeforeViewChange", function (old_mode, old_date, mode, date) {
                var year = date.getFullYear();
                var month = (date.getMonth() + 1);
                var d = new Date(year, month, 0);
                var daysInMonth = d.getDate();
                scheduler.matrix["timeline"].x_size = daysInMonth;
                return true;
            });

            scheduler.templates.event_class = function (start, end, event) {
                return "event_" + (event.status || "");
            };

            function getBookingStatus(key) {
                var bookingStatus = findInArray(bookingStatusesArr, key);
                return !bookingStatus ? '' : bookingStatus.label;
            }

            function getPaidStatus(isPaid) {
                return isPaid ? "Paid" : "Not paid";
            }

            var eventDateFormat = scheduler.date.date_to_str("%d %M %Y");
            scheduler.templates.event_bar_text = function (start, end, event) {
                return '';
                // var paidStatus = getPaidStatus(event.is_paid);
                // var startDate = eventDateFormat(event.start_date);
                // var endDate = eventDateFormat(event.end_date);
                // return [event.text + "<br />",
                // 	startDate + " - " + endDate,
                // 	"<div class='booking_status booking-option'>" + getBookingStatus(event.status) + "</div>",
                // 	"<div class='booking_paid booking-option'>" + paidStatus + "</div>"].join("");
            };

            scheduler.templates.tooltip_text = function (start, end, event) {
                var room = getRoom(event.room) || {label: ""};

                var html = [];
                // html.push("Booking: <b>" + event.text + "</b>");
                html.push("Room: <b>" + room.label + "</b>");
                html.push("Check-in: <b>" + eventDateFormat(start) + "</b>");
                html.push("Departure: <b>" + eventDateFormat(end) + "</b>");
                html.push(getBookingStatus(event.status) + ", " + getPaidStatus(event.is_paid));
                return html.join("<br>")
            };

            scheduler.templates.lightbox_header = function (start, end, ev) {
                var formatFunc = scheduler.date.date_to_str('%d.%m.%Y');
                return formatFunc(start) + " - " + formatFunc(end);
            };

            scheduler.attachEvent("onEventCollision", function (ev, evs) {
                for (var i = 0; i < evs.length; i++) {
                    if (ev.room != evs[i].room) continue;
                    dhtmlx.message({
                        type: "error",
                        text: "This room is already booked for this date."
                    });
                }
                return true;
            });

            scheduler.attachEvent('onEventCreated', function (event_id) {
                console.log('tik');
                var ev = scheduler.getEvent(event_id);
                ev.status = 1;
                ev.is_paid = false;
                // ev.text = 'new booking';
                // console.log(ev);
            });

            scheduler.addMarkedTimespan({days: [0, 6], zones: "fullday", css: "timeline_weekend"});

            window.updateSections = function updateSections(value) {
                var currentRoomsArr = [];
                if (value == 'all') {
                    scheduler.updateCollection("currentRooms", roomsArr.slice());
                    return
                }
                for (var i = 0; i < roomsArr.length; i++) {
                    if (value == roomsArr[i].type) {
                        currentRoomsArr.push(roomsArr[i]);
                    }
                }
                scheduler.updateCollection("currentRooms", currentRoomsArr);
            };

            scheduler.attachEvent("onXLE", function () {
                updateSections("all");

                var select = document.getElementById("room_filter");
                var selectHTML = ["<option value='all'>All</option>"];

                roomTypesArr.forEach(function (item, i, arr) {
                    // console.log( i + ": " + item.id + " (массив:" + arr + ")" );
                    selectHTML.push("<option value='" + item.id + "'>" + getRoomType(item.id) + "</option>");
                });

                // for (var i = 1; i < roomTypesArr.length + 1; i++) {
                //     selectHTML.push("<option value='" + i + "'>" + getRoomType(i) + "</option>");
                // }
                select.innerHTML = selectHTML.join("");
            });

            scheduler.attachEvent("onEventSave", function (id, ev, is_new) {
                if (!ev.fio) {
                    dhtmlx.alert("Full name is required!");
                    return false;
                }
                return true;
            });


            scheduler.clearAll();
            scheduler.init('scheduler_here', new Date(), "timeline");
            scheduler.load(ajaxurl + "?action=hb_get_data", "json");
            // if ( typeof window.dp == 'undefined') {
            //     window.dp = new dataProcessor(ajaxurl + "?action=hb_get_test_data");
            //     dp.init(scheduler);
            // }
            //

            let element = document.getElementById("scheduler_here");
            let top = scheduler.xy.nav_height + 1 + 1;
            let height = scheduler.xy.scale_height;
            let width = scheduler.matrix.timeline.dx;
            let header = document.createElement("div");
            header.className = "collection_label";
            header.style.position = "absolute";
            header.style.top = top + "px";
            header.style.width = width + "px";
            header.style.height = height + "px";

            var descriptionHTML = "<div class='timeline_item_separator'></div>" +
                "<div class='timeline_item_cell'>Room</div>" +
                "<div class='timeline_item_separator'></div>" +
                "<div class='timeline_item_cell'>Type</div>" +
                "<div class='timeline_item_separator'></div>" +
                "<div class='timeline_item_cell room_status'>Status</div>";
            header.innerHTML = descriptionHTML;
            element.appendChild(header);


        },
        methods: {},
        template: `<div>
            <div id="scheduler_here" class="dhx_cal_container" style='width:100%; height:100%;'>
                <div class="dhx_cal_navline">
                    <div style="font-size:16px;padding:4px 20px;">
                        Filter by Type:
                        <select id="room_filter" onchange='updateSections(this.value)'></select>
                    </div>
                    <div class="dhx_cal_prev_button">&nbsp;</div>
                    <div class="dhx_cal_next_button">&nbsp;</div>
                    <div class="dhx_cal_today_button"></div>
                    <div class="dhx_cal_date"></div>
                </div>
                <div class="dhx_cal_header"></div>
                <div class="dhx_cal_data">
                </div>
            </div>
        </div>`
    }

    const RoomTypes = {
        data() {
            return {
                roomTypes: {},
            };
        },
        mounted() {
            axios
                .get(ajaxurl + '?action=hb_get_room_types')
                .then(response => (this.roomTypes = response.data))
                .catch(error => console.log(error));
        },
        template: `<div>

        <a href="#TB_inline?width=500&inlineId=modal-window-add" class="button button-primary thickbox" style="float:right;">
            <span class="dashicons dashicons-plus" style="line-height: 32px;" ></span>
        </a>
        <div id="modal-window-add" style="display:none;">
            <div style="padding-top:5px;">

                <input type="text" placeholder="Title">
                <input type="text" placeholder="Shortcode">
                <input type="text" placeholder="Area"> м<sup>2</sup>

                <h4 style="margin-bottom:0;">Доп. Услуги</h4>
                <label class="col-form-label">
                    <input type="checkbox" name="add_services[]" value="2" checked=""> Трансфер 1
                </label>
                <label class="col-form-label">
                    <input type="checkbox" name="add_services[]" value="3" checked=""> Массаж
                </label>
                <label class="col-form-label">
                    <input type="checkbox" name="add_services[]" value="4" checked=""> Обед
                </label>
                <label class="col-form-label">
                    <input type="checkbox" name="add_services[]" value="5" checked=""> Ужин
                </label>


                <div class="form-group row">
                    <div class="col">
                        <input type="text" class="form-control" name="capacity[1]" value="" placeholder="Цена за 1">
                    </div>
                    <div class="col col-form-label">
                        1
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col">
                        <input type="text" class="form-control" name="capacity[1 + 1]" value="" placeholder="Цена за 1 + 1">
                    </div>
                    <div class="col col-form-label">
                        1 + 1
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col">
                        <input type="text" class="form-control" name="capacity[1 + 2]" value="" placeholder="Цена за 1 + 2">
                    </div>
                    <div class="col col-form-label">
                        1 + 2
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col">
                        <input type="text" class="form-control" name="capacity[1 + 3]" value="" placeholder="Цена за 1 + 3">
                    </div>
                    <div class="col col-form-label">
                        1 + 3
                    </div>
                </div>

                Изображения
                <div class="row" id="images_result"></div>
                <input type="file" class="form-control" onchange="images_upload()" name="images[]" accept="image/jpeg,image/png,image/gif" multiple="">
                <div class="progress progress_images" style="display:none">
                    <div class="images-bar progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                </div>



                <h4>Удобства</h4>
                <label>
                    <input type="checkbox" name="comfort[]" value="5" checked=""> Вайфай
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="6" checked=""> Кондиционер
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="7" checked=""> холодильник
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="8" checked=""> сейф
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="9" checked=""> халат
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="10" checked=""> фен
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="11" checked=""> шкаф гардероб
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="12" checked=""> тапочки
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="13" checked=""> набор полотенец
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="14" checked=""> телевизор
                </label>
                <label>
                    <input type="checkbox" name="comfort[]" value="15" checked=""> балкон
                </label>

                <input type="text" placeholder="Вместимость Рус">
                <textarea rows="3" placeholder="Описание Рус"></textarea>

                <button type="submit" name="add" class="btn btn-lg btn-block btn-success form-group" data-loading-text="Добавляется...">
                    Добавить
                </button>

            </div>
        </div>

        <h3>Room Types</h3>

        <table class="widefat striped">
            <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Area</th>
                <th>Capacity</th>
                <th>Price</th>
                <th style="width:50px"></th>
                <th style="width:50px"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in roomTypes">
                <td class="align-middle">
                    #
                </td>
                <td class="align-middle">
                    <b>{{ item.shortcode }}</b><br>
                    {{ item.title }}
                </td>
                <td class="align-middle">
                    {{ item.area }}
                </td>
                <td class="align-middle">
                    <div v-for="(cap, ci ) in JSON.parse(item.capacity)">
                     {{ cap ? ci : ''  }}
                    </div>
                </td>
                <td class="align-middle">
                    <div v-for="(cap, ci ) in JSON.parse(item.capacity)">
                     {{ cap }}
                    </div>
                </td>
                <td style="text-align:right;">
                    <a href="#" class="button button-primary button-small" style="background: #FFB900;border:1px solid #FFB900;">
                        <span class="dashicons dashicons-edit" style="line-height: 24px;"></span>
                    </a>
                </td>
                <td style="text-align:right;">
                    <a href="#" class="button button-primary button-small" style="background: #DC3232;border:1px solid #DC3232;">
                        <span class="dashicons dashicons-trash" style="line-height: 24px;"></span>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>

        </div>`
    }

    const Rooms = {
        data() {
            return {
                groupRooms: {},
            };
        },
        mounted() {
            axios
                .get(ajaxurl + '?action=hb_get_rooms')
                .then(response => (this.groupRooms = response.data))
                .catch(error => console.log(error));
        },
        template: `<div>

        <a href="#TB_inline?width=500&height=45&inlineId=modal-window-add" class="button button-primary thickbox" style="float:right;">
            <span class="dashicons dashicons-plus" style="line-height: 32px;" ></span>
        </a>
        <div id="modal-window-add" style="display:none;">
            <div style=" padding-top:5px;">
                <input type="text" placeholder="Название">
                <select>
                    <option value="3">Стандартный номер</option><option value="4">Номер люкс</option>
                </select>
                <select>
                    <option value="1">Готов</option><option value="2">Уборка</option><option value="3">Грязный</option>
                </select>
                <button type="button" class="button button-primary">
                    Add New Room
                </button>
            </div>
        </div>

        <div v-for="(rooms, index) in groupRooms">
            <h3 for class="mt-3">{{ index }}</h3>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th>Room</th>
                    <th class="w-50">Type room</th>
                    <th>Status</th>
                    <th style="width:50px"></th>
                    <th style="width:50px"></th>
                    <th style="width:50px"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item, index) in rooms">
                    <td class="align-middle">
                        {{ item.name }}
                    </td>
                    <td class="align-middle">
                        {{ item.type }}
                    </td>
                    <td class="align-middle">
                        {{ item.cleaner }}
                    </td>
                    <td style="text-align:right;">
                        <a href="#" title="Активен" class="button button-primary button-small" style="background:#46B450; border:1px solid #46B450">
                            <span class="dashicons dashicons-visibility" style="line-height: 24px;"></span>
                        </a>
                    </td>
                    <td style="text-align:right;">
                        <a href="#" class="button button-primary button-small" style="background:#FFB900; border:1px solid #FFB900">
                            <span class="dashicons dashicons-edit" style="line-height: 24px;"></span>
                        </a>
                    </td>
                    <td style="text-align:right;">
                        <a href="#" class="button button-primary button-small" style="background:#DC3232; border:1px solid #DC3232">
                            <span class="dashicons dashicons-trash" style="line-height: 24px;"></span>
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        </div>`
    }

    const Orders = {
        data() {
            return {
                orders: {},
            };
        },
        mounted() {
            axios
                .get(ajaxurl + '?action=hb_get_orders')
                .then(response => (this.orders = response.data))
                .catch(error => console.log(error));
        },
        template: `<div>

        <h3>Orders</h3>

        <table class="widefat striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Room</th>
                <th style="width:200px;">Check-in - Check-out date</th>
                <th>Price</th>
                <th>Status</th>
                <th style="width:50px"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in orders">
                 <td>{{ item.id }}</td>
                <td>
                    {{ item.fio }}<br>
                    Email: {{ item.email }} <br>Noty: {{ item.noty }}, дней: 2, доп.услуги(Трансфер 1|) , прибытие: 12:00, завтрак: yes, парковка: yes
                </td>
                <td>
                    {{ item.tel }}
                </td>
                <td>
                   {{ item.room }}
                </td>
                <td>
                    {{ item.start_date }} - {{ item.end_date }}
                </td>
                <td>
                    {{ item.cost }}
                </td>
                <td>
                     {{ item.status }} - {{ item.is_paid }}
                </td>
                <td style="text-align:right;">
                    <a href="#" class="button button-primary button-small" style="background: #DC3232;border:1px solid #DC3232;">
                        <span class="dashicons dashicons-trash" style="line-height: 24px"></span>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
        </div>`
    }

    const Settings = {
        data() {
            return {
                settings: {},
                tmpCur: {},
                tmpPromo: {},
            }
        },
        mounted() {
            axios
                .get(ajaxurl + '?action=hb_get_settings')
                .then(response => (this.settings = response.data))
                .catch(error => console.log(error));
        },
        methods: {
            changeData() {
                // console.log(this.settings);
                axios
                    .post(ajaxurl + '?action=hb_store_settings', Qs.stringify(this.settings))
                    .then(response => this.settings = response.data)
                    .catch(error => console.log(error));

                // jQuery.ajax({
                //     type: "POST",
                //     url: ajaxurl + '?action=hb_store_settings',
                //     data: this.settings,
                //     success: {},
                //     dataType: 'json'
                // });

            },
            addNewCur() {
                this.settings.CUR.push(this.tmpCur);
                this.tmpCur = {};
                this.changeData();
            },
            addNewPromo() {
                this.settings.PROMO.push(this.tmpPromo);
                this.tmpPromo = {};
                this.changeData();
            }
        },
        template: `<div>
            <h3>Settings</h3>

            <div style="width:66%; float:left;">

                <table class="widefat striped">
                    <tr>
                        <th>Room Statuses</th>
                        <td>
                            <input-tag v-model="settings.ROOM_STATUSES" @input="changeData" :add-tag-on-blur="true"></input-tag>
                        </td>
                    </tr>
                    <tr>
                        <th>Booking Statuses</th>
                        <td>
                            <input-tag v-model="settings.BOOKING_STATUS" @input="changeData" :add-tag-on-blur="true"></input-tag>
                        </td>
                    </tr>
                    <tr>
                        <th>Comforts List</th>
                        <td>
                            <input-tag v-model="settings.COMFORTS_LIST" @input="changeData" :add-tag-on-blur="true"></input-tag>
                        </td>
                    </tr>
                    <tr>
                        <th>Services List</th>
                        <td>
                            <input-tag v-model="settings.SERVICES_LIST" @input="changeData" :add-tag-on-blur="true"></input-tag>
                        </td>
                    </tr>
                    <tr>
                        <th>Sets List</th>
                        <td>
                            <input-tag v-model="settings.SETS_LIST" @input="changeData" :add-tag-on-blur="true"></input-tag>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Room Images, px</label></th>
                        <td>
                            Large
                            <input style="width:100px; margin-right:15px;" type="number" @input="changeData" v-model="settings.IMG_LARGE">
                            Medium
                            <input style="width:100px; margin-right:15px;" type="number" @input="changeData" v-model="settings.IMG_MEDIUM">
                            Small
                            <input style="width:100px;" type="number" @input="changeData" v-model="settings.IMG_SMALL">
                        </td>
                    </tr>
                </table>

            </div>
            <div style="width:32%; float:left; margin-left:1%;">

                <table class='widefat '>
                    <thead>
                        <tr>
                            <th>Currencies</th>
                            <th>Sign</th>
                            <th>Coef</th>
                            <th style="width:30px; text-align: center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(rowList, index0) in settings.CUR" >
                          <td v-for="(item, index1) in rowList" @change="changeData"><input type="text" style="width:100%;" :value="item" v-model="settings.CUR[index0][index1]" ></td>
                          <td><button @click="settings.CUR.splice(index0, 1);changeData()" class="button" style="background: #DC3232;color: #fff; line-height: normal; border-color:#DC3232;" type="submit"><span class="dashicons dashicons-trash"></span></button></td>
                        </tr>
                    </tbody>
                    <tr style="background:#f0f6fc;">
                        <td><input v-model="tmpCur[0]" type="text" style="width:100%;" placeholder="Currency" ></td>
                        <td><input v-model="tmpCur[1]" type="text" style="width:100%;" placeholder="Sign" ></td>
                        <td><input v-model="tmpCur[2]" type="text" style="width:100%;" placeholder="Coef" ></td>
                        <td><button @click="addNewCur" class="button-primary button" style="line-height: normal;" type="button"><span class="dashicons dashicons-plus"></span></button></td>
                    </tr>
                </table>

                <br/>

                <table class='widefat '>
                    <thead>
                    <tr>
                        <th>Promocodes</th>
                        <th>Sum</th>
                        <th>Status</th>
                        <th style="width:30px; text-align: center"></th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(rowList, index0) in settings.PROMO" >
                          <td v-for="(item, index1) in rowList" @change="changeData"><input type="text" style="width:100%;" :value="item" v-model="settings.PROMO[index0][index1]"></td>
                          <td><button  @click="settings.PROMO.splice(index0, 1);changeData()" class="button" style="background: #DC3232;color: #fff; line-height: normal; border-color:#DC3232;" type="submit"><span class="dashicons dashicons-trash"></span></button></td>
                        </tr>
                    </tbody>
                    <tr style="background:#f0f6fc;">
                        <td><input v-model="tmpPromo[0]" type="text" style="width:100%;" placeholder="Promocode" ></td>
                        <td><input v-model="tmpPromo[1]" type="text" style="width:100%;" placeholder="Sum" ></td>
                        <td><input v-model="tmpPromo[2]" type="text" style="width:100%;" placeholder="Status" ></td>
                        <td><button @click="addNewPromo" class="button-primary button" style="line-height: normal;" type="button"><span class="dashicons dashicons-plus"></span></button></td>
                    </tr>
                </table>


            </div>


        </div>`
    }

    NProgress.configure({ parent: '#app' });

    axios.interceptors.request.use(config => {
        NProgress.start()
        return config
    })

    axios.interceptors.response.use(response => {
        NProgress.done()
        return response
    })

    const router = new VueRouter({
        routes: [
            {path: "/", component: Dashboard,},
            {path: "/room_types", component: RoomTypes,},
            {path: "/rooms", component: Rooms,},
            {path: "/orders", component: Orders,},
            {path: "/settings", component: Settings,},
        ]
    });

    Vue.component('input-tag', vueInputTag.default)

    const app = new Vue({
        router,
        el: '#app',
        data() {
        },
        mounted() {
        }
    });
</script>
