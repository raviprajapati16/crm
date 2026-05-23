<?php
defined('BASEPATH') or exit('No direct script access allowed');
$currentChatColor = '#456e36';
$goals = [];
if (is_staff_member()) {
    $this->load->model('goals/goals_model');
    $goals = $this->goals_model->get_staff_goals(get_staff_user_id());
}
?>
<?php if (!empty($goals)) { ?>
    <style>
        #goalPopupView #membersContent .online.isToggled .fa-trophy {
            display: block;
            position: absolute;
            top: 0px;
            font-size: 32px;
            left: 30px;
            padding: 10px;
            color: #fff;
            border-radius: 50%;
        }

        #goalPopupView {
            z-index: 9999;
            position: fixed;
            right: 0;
            bottom: 0;
            font-family: Helvetica Neue, Segoe UI, Helvetica, Arial, sans-serif !important;
            font-size: 12px;
            color: #515151;
            width: 100%;
        }

        #goalPopupView input.chat_color::-webkit-input-placeholder {
            font-size: 10.5px;
        }

        #menu .liveUsers {
            margin-left: 8px !important;
            text-transform: capitalize;
            background-color: #546bf2;
            border-color: #ffffff;
            border: 1px solid;
        }

        #menu .liveClients {
            background-color: #535fa5;
            border-color: #ffffff;
            border: 1px solid;
        }

        body .lity {
            z-index: 999999999 !important;
        }

        #goalPopupView .connection_field {
            display: none;
            background: #f03d25;
            height: 44px;
            position: absolute;
            color: #fff;
            text-align: center;
            padding-top: 7px;
            left: 0;
            top: 0;
            border-top-right-radius: 4px;
            right: 0;
            border-top-left-radius: 4px;
        }

        #goalPopupView .connection_field i {
            font-size: 22px;
            margin-top: 2px;
        }

        #goalPopupView .blink {
            animation: blinker 1s linear infinite;
        }

        @keyframes blinker {
            50% {
                opacity: 0;
            }
        }

        #goalPopupView #membersContent {
            position: absolute;
            z-index: 99999;
            right: 70px;
            bottom: 0px;
            width: 310px;
            background: #eee;
            border-top-left-radius: 9px;
            border-top-right-radius: 9px;
        }

        #goalPopupView #membersContent a {
            position: relative;
            background: #fff;
            color: #515151;
            display: block;
            width: available;
            text-decoration: none;
            padding: 0px 8px 0px 0px;
            height: 32px;
            line-height: 32px;
            text-indent: 10px;
            font-size: 14px;
        }

        #goalPopupView #membersContent .fa.fa-search {
            color: #fff;
            position: absolute;
            left: 12px;
            bottom: 9px;
            cursor: pointer;
            font-size: 18px;
        }

        #goalPopupView #membersContent a.off {
            background: #eee;
            color: #333 !important;
        }

        #goalPopupView .onlineUsername {
            color: #333;
        }

        #goalPopupView #membersContent a:hover>span {
            color: #fff;
        }

        #goalPopupView #membersContent a.on {
            transition: all 0.2s;
            -webkit-transition: all 0.2s;
            -moz-transition: all 0.2s;
            -ms-transition: all 0.2s;
            -o-transition: all 0.2s;
        }

        #goalPopupView #membersContent .pushUp {
            cursor: pointer;
            position: absolute;
            margin-top: -1.5px;
            left: 86%;
            font-size: 17px;
        }

        #goalPopupView #membersContent .topInfo {
            border-radius: 4px 4px 0 0;
            cursor: pointer;
            text-align: center;
            height: 36px;
            background-color: #343a40;
            color: white;
        }

        #goalPopupView .userName {
            padding-left: 3px;
            font-size: 15px;
            display: inline-block;
            padding-top: 5px;
        }

        #goalPopupView #membersContent .scroll::-webkit-scrollbar {
            width: 8px;
        }

        #goalPopupView #membersContent .scroll::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.3);
        }

        #goalPopupView #membersContent .scroll {
            overflow-y: auto;
            overflow-x: hidden;
            max-height: 19vw;
        }



        #goalPopupView .fa-minus,
        #goalPopupView .fa-close,
        #goalPopupView .fa-window-maximize {
            color: white;
            font-size: 18px;
            width: 12px;
        }

        #goalPopupView #membersContent .fa-minus {
            color: white;
            font-size: 15px;
            width: 12px;
            position: absolute;
            right: 1px;
            padding-top: 4px;
        }

        #goalPopupView .chatMain .online {
            padding-bottom: 4px;
        }

        #membersContent>div.chat-footer>div.online {
            display: inline-block;
            width: 66%;
            font-size: 16px;
            padding-top: 3px;
        }

        #goalPopupView .enterBtn i {
            color: #1b7fe3;
            font-size: 16px;
        }

        #goalPopupView .enterBtn {
            cursor: pointer;
            position: absolute;
            bottom: 6px;
            right: 8px;
        }

        #goalPopupView .fileUpload {
            cursor: pointer;
            position: absolute;
            font-size: 20px;
            bottom: 3px;
            right: 29px;
        }

        #goalPopupView form[name=pusherMessagesForm] {
            margin-bottom: -2px;
        }


        #goalPopupView #membersContent span.open .fa-window-maximize {
            color: white;
            font-size: 12px;
        }

        #goalPopupView .msgTxt:last-child,
        #goalPopupView .msgTxt:last-child .you {
            margin-bottom: 10px;
        }

        #goalPopupView .addFile {
            position: absolute;
            color: #659FDA;
            top: 4px;
            left: 4px;
        }

        #goalPopupView p .prchat_convertedImage {
            width: 100%;
        }

        #goalPopupView #membersContent a img {
            width: 30px;
            height: 30px;
            -moz-border-radius: 15px;
            -webkit-border-radius: 15px;
            border-radius: 15px;
            float: right;
            margin-top: 1px;
        }

        #goalPopupView form[name=pusherMessagesForm] {
            margin-bottom: -6px;
        }

        #goalPopupView i.fa.fa-plus-circle {
            font-size: 20px;
            color: #1c7fe3;
        }



        #goalPopupView #membersContent .off::before {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            background-color: #5d6061;
            margin-right: 10px;
        }



        #goalPopupView #membersContent .on::before {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            background-color: #00dc6c;
            margin-right: 10px;
        }


        #goalPopupView .emptyMsg {
            position: absolute;
            color: black;
            width: 200px;
            left: 3px;
            top: 30px;
        }

        #goalPopupView a {
            color: #fff;
            text-decoration: underline;
        }

        #goalPopupView .fa.fa-th-large.user_view {
            color: #fff;
            margin-right: 24px;
            font-size: 17px;
            float: none;
            margin-top: 5px;
            padding: 0;
        }

        #goalPopupView .fa.fa-th-large {
            padding-top: 9px;
            float: right;
            font-size: 19px;
            padding-right: 11px;
        }

        #goalPopupView #members-list a {
            font-size: 16px;
            margin-bottom: 0;
            height: 35px;
            padding-top: 2px;
        }

        #goalPopupView p.cname {
            margin: 0;
            float: left;
            font-size: 15px;
            padding-top: 9px;
            padding-left: 16px;
            letter-spacing: 0.2px;
        }

        #goalPopupView .chatDateMe {
            color: #d7d7d7;
            font-size: 10px;
            float: right;
            padding-right: 10px;
        }

        #goalPopupView .chatDateFriend {
            color: #d7d7d7;
            font-size: 10px;
            float: left;
        }

        #goalPopupView .convertedLink {
            font-weight: 600;
            color: black;
        }

        #goalPopupView .chatUsername {
            color: #000;
            float: right;
            clear: both;
            padding-right: 10px;
            padding-bottom: 3px;
            padding-top: 3px;
            font-size: 11px;
        }

        #goalPopupView .chatFriendUsername {
            color: #000;
            float: left;
            clear: both;
            font-size: 11px;
            padding-bottom: 3px;
            padding-left: 10px;
            padding-top: 0px;
        }

        #goalPopupView .conversation_me {
            padding-right: 17px;
        }

        #goalPopupView .conversation_from {
            padding-left: 20px;
        }

        #goalPopupView .myProfilePic {
            width: 26px;
            height: 26px;
            position: relative;
            float: right;
            clear: both;
            left: 20px;
            top: 5px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
        }

        #goalPopupView .friendProfilePic {
            width: 26px;
            height: 26px;
            position: relative;
            float: left;
            clear: both;
            right: 20px;
            /* top: 18px; */
            top: 5px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
        }

        #goalPopupView .search_hidden {
            display: none;
        }

        #goalPopupView .searchBox {
            height: 28px;
            -moz-border-radius: 0px;
            -webkit-border-radius: 0px;
            border-radius: 0px;
            border: 0px;
            border-bottom: 1px solid gray;
        }

        #goalPopupView .searchBox:focus {
            color: #495057;
            box-shadow: 0 0 0 0.0rem;
            -moz-box-shadow: 0 0 0 0.0rem;
            -webkit-box-shadow: 0 0 0 0.0rem;
        }

        #goalPopupView .notification-box {
            position: absolute;
            z-index: 99;
            top: 5px;
            right: -86px;
            padding: 0px !important;
            border: 0px !important;
            text-align: center;
        }

        #goalPopupView .notification-bell {
            animation: bell 1s 1s both infinite;
            padding-right: 1px;
            padding-top: 4px;
        }

        #goalPopupView .notification-bell * {
            display: block;
            margin: 0 auto;
            background-color: #fff;
        }

        #goalPopupView .bell-top {
            width: 3px;
            height: 3px;
            -moz-border-radius: 2px 2px 0 0;
            -webkit-border-radius: 2px 2px 0 0;
            border-radius: 2px 2px 0 0;
            margin-top: 1px;
        }

        #goalPopupView .bell-middle {
            width: 8px;
            height: 8px;
            margin-top: -1px;
            -moz-border-radius: 12.5px 12.5px 0 0;
            -webkit-border-radius: 12.5px 12.5px 0 0;
            border-radius: 12.5px 12.5px 0 0;
        }

        #goalPopupView .bell-bottom {
            -moz-border-radius: 4px 4px 4px 4px;
            -webkit-border-radius: 4px 4px 4px 4px;
            border-radius: 4px 4px 4px 4px;
            position: relative;
            z-index: 0;
            width: 12px;
            height: 2px;
        }

        #goalPopupView .bell-bottom::before,
        #goalPopupView .bell-bottom::after {
            content: '';
            position: absolute;
            top: -4px;
        }

        #goalPopupView .bell-bottom::before {
            left: 1px;
            border-bottom: 4px solid #fff;
            border-right: 0 solid transparent;
            border-left: 4px solid transparent;
        }

        #goalPopupView .bell-bottom::after {
            right: 1px;
            border-bottom: 4px solid #fff;
            border-right: 4px solid transparent;
            border-left: 0 solid transparent;
        }

        #goalPopupView .bell-rad {
            width: 3px;
            height: 3px;
            margin-top: -2px;
            border-radius: 0 0 4px 4px;
            animation: rad 1s 2s both infinite;
        }

        #goalPopupView .notification-count {
            position: absolute;
            z-index: 1;
            top: -1px;
            right: 6px;
            width: 13px;
            height: 13px;
            line-height: 14px;
            font-size: 9px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            background-color: #f94a5a;
            color: #fff;
            animation: zoom 3s 3s both infinite;
        }

        #goalPopupView .unread-notifications[data-badge]:after {
            content: attr(data-badge);
            position: absolute;
            text-indent: 0px;
            left: 86%;
            top: 49%;
            font-size: 11px;
            background: #6072f3;
            color: white;
            width: 16px;
            height: 16px;
            text-align: center;
            line-height: 17px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            animation: zoom 2s 2s both infinite;
        }

        @-moz-document url-prefix() {
            #goalPopupView .jscolor {
                width: 221px
            }
        }

        #goalPopupView .chatBoxWrap {
            float: left;
            position: relative
        }


        #goalPopupView #templateChatBox {
            display: none;
        }

        #goalPopupView .chatBoxslide {
            float: right;
            position: relative;
            z-index: 9999;
        }

        #goalPopupView .chatBoxWrap #slideLeft .fa-angle-double-left,
        #goalPopupView .chatBoxWrap #slideRight .fa-angle-double-right {
            color: #00B4FF;
            top: -2px;
            position: absolute;
            font-size: 25px;
            font-weight: bold;
        }

        #goalPopupView .chatBoxWrap #slideLeft,
        #goalPopupView .chatBoxWrap #slideRight {
            display: none;
            position: absolute;
            font-size: 18px;
            top: -24px;
        }

        #goalPopupView .chatBoxWrap #slideLeft {
            left: 10px;
        }

        #goalPopupView .chatBoxWrap #slideRight {
            right: 2px;
            color: #00B4FF;
        }

        #goalPopupView .chatBoxWrap .overFlowHide {
            display: none
        }

        #goalPopupView .fa-volume-up,
        #goalPopupView .fa-volume-off {
            color: white;
            cursor: pointer;
            position: absolute;
            right: 9px;
            font-size: 20px;
            bottom: 8px;
        }

        #goalPopupView .message_loader {
            animation: rotate 2s linear infinite;
            z-index: 2;
            position: absolute;
            top: 0%;
            left: 48%;
            width: 20px;
            height: 20px;
            opacity: 1;
        }

        #goalPopupView .path {
            stroke: #007bff;
            stroke-linecap: round;
            animation: dash 1.5s ease-in-out infinite;
        }

        @keyframes rotate {
            100% {
                transform: rotate(360deg);
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
            }
        }

        #goalPopupView .chat_color {
            height: 30px;
            font-size: 12px;
            -moz-border-radius: 0px;
            -webkit-border-radius: 0px;
            border-radius: 0px;
            top: -6px;
            position: absolute;
            left: 118px;
            width: 195px;
            background: #FFF;
        }

        #goalPopupView .emoji {
            width: 20px;
            height: 20px;
            display: inline-block;
            margin-bottom: 1px;
            background-size: contain;
        }

        #goalPopupView .colorHolder {
            position: absolute;
            left: -4px;
            top: -26px;
        }

        #goalPopupView button#chColor::focus,
        #goalPopupView button#chGradientColor::focus {
            box-shadow: 0px 0px 0px 0px transparent;
            -moz-box-shadow: 0px 0px 0px 0px transparent;
            -webkit-box-shadow: 0px 0px 0px 0px transparent;
        }

        #goalPopupView button#chGradientColor,
        #goalPopupView button#chColor {
            text-transform: capitalize;
            padding: 0px;
            height: 30px;
            border-radius: 0px;
            -moz-border-radius: 0px;
            -webkit-border-radius: 0px;
            border: none;
            outline: none !important;
            background: #6c6d6d;
            color: white;
            margin-left: 51px;
            margin-top: -6px;
            width: 66px;
            font-size: 13px;
            -ms-border-radius: 0px;
            -o-border-radius: 0px;
            -moz-border-radius: 0px;
        }

        #goalPopupView input.jscolor {
            box-shadow: none;
            outline: none !important;
            border: none;
            -webkit-border-top-left-radius: 0px !important;
            border-top-left-radius: 0px !important;
            -webkit-border-bottom-left-radius: 0px !important;
            border-bottom-left-radius: 0px !important;
            border-left: 1px solid #ffffff !important;
        }

        #goalPopupView .chat-footer {
            cursor: pointer;
            cursor: -moz-pointer;
            cursor: -webkit-pointer;
            z-index: 2;
            height: 36px;
            background: #3C5F94;
            color: white;
            text-align: center;
            padding-top: 5px;
            padding-bottom: 22px;
        }

        #goalPopupView .chat-footer>span {
            font-size: 13px;
            letter-spacing: 0.2px;
        }

        #goalPopupView .tooltip {
            z-index: 999999999999 !important;
            line-height: 0.8;
            font-size: 13px;
        }

        #goalPopupView .bounce {
            -moz-animation: bounce 3s infinite;
            -webkit-animation: bounce 3s infinite;
            animation: bounce 3s infinite;
        }

        #goalPopupView .conversation_from .friend a {
            color: black;
        }

        #goalPopupView span.chat_options {
            height: 0;
            cursor: pointer;
        }

        #goalPopupView div.message_container {
            clear: both;
            margin-top: 0px;
            width: 244px;
            /* height: 50px; */
        }

        #goalPopupView .confirm_delete {
            width: 25px;
            color: #94303094;
            font-size: 19px;
            padding-top: 1px;
            padding-left: 3px;
        }

        #goalPopupView .confirm_delete:hover::before {
            color: #e2192e;
        }

        #goalPopupView span.show_delete {
            display: none;
            margin-top: 3px;
        }

        #goalPopupView .tooltip {
            z-index: 999999;
        }

        #goalPopupView .italic_small {
            font-style: italic;
            font-size: 15px;
        }

        @keyframes chat-module-loader {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @-moz-keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                -moz-transform: translateY(0);
                transform: translateY(0);
            }

            40% {
                -moz-transform: translateY(-30px);
                transform: translateY(-30px);
            }

            60% {
                -moz-transform: translateY(-15px);
                transform: translateY(-15px);
            }
        }

        @-webkit-keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }

            40% {
                -webkit-transform: translateY(-30px);
                transform: translateY(-30px);
            }

            60% {
                -webkit-transform: translateY(-15px);
                transform: translateY(-15px);
            }
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                -moz-transform: translateY(0);
                -ms-transform: translateY(0);
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }

            40% {
                -moz-transform: translateY(-2px);
                -ms-transform: translateY(-2px);
                -webkit-transform: translateY(-2px);
                transform: translateY(-2px);
            }

            60% {
                -moz-transform: translateY(-3px);
                -ms-transform: translateY(-3px);
                -webkit-transform: translateY(-3px);
                transform: translateY(-3px);
            }
        }

        @keyframes dash {
            0% {
                stroke-dasharray: 1, 150;
                stroke-dashoffset: 0;
            }

            50% {
                stroke-dasharray: 90, 150;
                stroke-dashoffset: -35;
            }

            100% {
                stroke-dasharray: 90, 150;
                stroke-dashoffset: -124;
            }
        }

        @keyframes bell {
            0% {
                transform: rotate(0);
            }

            10% {
                transform: rotate(30deg);
            }

            20% {
                transform: rotate(0);
            }

            80% {
                transform: rotate(0);
            }

            90% {
                transform: rotate(-30deg);
            }

            100% {
                transform: rotate(0);
            }
        }

        @keyframes rad {
            0% {
                transform: translateX(0);
            }

            10% {
                transform: translateX(6px);
            }

            20% {
                transform: translateX(0);
            }

            80% {
                transform: translateX(0);
            }

            90% {
                transform: translateX(-6px);
            }

            100% {
                transform: translateX(0);
            }
        }

        @keyframes zoom {
            0% {
                opacity: 0;
                transform: scale(0);
            }

            10% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 1;
            }

            51% {
                opacity: 0;
            }

            100% {
                opacity: 0;
            }
        }

        #goalPopupView .chatMobileVisible {
            visibility: visible;
        }

        @media only screen and (max-width: 768px) {
            #goalPopupView .chatMobileVisible {
                visibility: hidden;
            }
        }


        /* isToggled class active main members toglled view */

        #goalPopupView .scroll.isToggled {
            display: none !important;
        }

        #goalPopupView .chat-footer.isToggled {
            width: 50px !important;
            height: 50px !important;
            border-radius: 14px;
            -webkit-border-radius: 14px;
            -moz-border-radius: 14px;
            -ms-border-radius: 14px;
            -o-border-radius: 14px;
        }

        #goalPopupView .toCircle {
            float: right;
            margin-top: -2px;
            font-size: 22px;
            margin-left: -30px;
            margin-right: -43px;
            -webkit-transition: all 0.2s cubic-bezier(1, 0.09, 0.07, 0.96);
            -moz-transition: all 0.2s cubic-bezier(1, 0.09, 0.07, 0.96);
            -ms-transition: all 0.2s cubic-bezier(1, 0.09, 0.07, 0.96);
            -o-transition: all 0.2s cubic-bezier(1, 0.09, 0.07, 0.96);
            transition: all 0.2s cubic-bezier(1, 0.09, 0.07, 0.96);
        }

        #goalPopupView .topInfo.isToggled,
        #goalPopupView #searchUsers.isToggled,
        #goalPopupView #disableSound.isToggled,
        #goalPopupView #colorChanger.isToggled {
            display: none;
        }

        #goalPopupView #membersContent.isToggled {
            background: none !important;
            width: 50px !important;
            height: 50px !important;
            -webkit-border-radius: 50px;
            border-radius: 50px;
        }

        #goalPopupView .online.isToggled {
            font-size: 0px !important;
        }

        #goalPopupView #membersContent .online.isToggled .fa-comments {
            display: block;
            position: absolute;
            top: 0px;
            font-size: 35px;
            left: 30px;
            padding: 8px;
            color: #fff;
            border-radius: 50%;
        }

        #goalPopupView .gradientButton.active.focus,
        #goalPopupView .gradientButton.active:hover,
        #goalPopupView .gradientButton:active:hover,
        #goalPopupView .gradientButton.open>.dropdown-toggle.btn-primary.focus,
        #goalPopupView .gradientButton.open>.dropdown-toggle.btn-primary:focus,
        #goalPopupView .gradientButton.open>.dropdown-toggle.btn-primary:hover,
        #goalPopupView .gradientButton {
            background: transparent !important;
            border: none !important;
        }

        #goalPopupView #colorChangerMenu {
            margin-left: -126px;
            margin-bottom: 5px;
            width: max-content;
        }

        #goalPopupView .closeColorButton {
            position: absolute;
            top: -6px;
            left: 5px;
            padding: 5px;
            font-size: 13px;
            background: #FD6969;
            border-radius: 0px;
            color: #f3f3f3;
            text-transform: capitalize;
        }

        #goalPopupView #colorChanger i {
            margin-left: -13px;
            margin-right: 12px;
        }

        #goalPopupView #colorGradient i,
        #goalPopupView #resetColors i {
            margin-left: -13px;
            margin-right: 11px;
        }

        #goalPopupView #resetColors i {
            margin-left: -13px;
            margin-right: 14px;
        }

        #goalPopupView .dropup {
            margin-top: -28px;
            margin-left: 38px;
            width: 12%;
        }

        #goalPopupView .goal {
            padding: 5px 5px 5px 5px;
            background: #fff;
            height: 100%;
            margin: 5px 10px 12px 10px;
            border-radius: 11px;
            border: 1px solid #456e36;
        }

        #goalPopupView .fa-times-circle {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 18px;
        }

        #goalPopupView .small-details {
            font-size: 10px;
            color: #555;
            position: relative;
            margin-top: -5%;
        }

        #goalPopupView .duration-title {
            position: relative;
            margin-top: 5%;
            font-size: 8.5px;
            font-weight: bold;
        }

        #goalPopupView .goal-percent {
            position: absolute;
            top: 5%;
            left: 1%;
            width: 100%;
            text-align: center;
            line-height: 40px;
            font-size: 10px;
        }

        #goalPopupView .progress-container {
            display: flex;
        }

        #goalPopupView .progress-section {
            margin-left: 5px;
            width: 19%;
            text-align: center;
        }

        #goalPopupView .widget-goal-progress {
            text-align: center;
            position: relative;
        }
    </style>
    <div id="goalPopupView">
        <div id="mainGoalId" class="draggable" style="display:none;">
            <div id="membersContent">
                <div class="chatMain">
                    <div class="topInfo" style="background:<?php echo $currentChatColor; ?>;">
                        <p class="cname"><i class="fa fa-trophy"></i> Goals</p>
                        <p class="pull-right" onclick="goalCircleTransform();"><i class="fa fa-times-circle" aria-hidden="true"></i></p>
                    </div>
                </div>
                <div class="scroll">
                    <div class="row">
                        <?php foreach ($goals as $goal) {
                            $typesArr = get_goal_duration_by_key($goal['goal_duration_type']);
                            if ($goal['goal_duration_type'] == "6") {
                                $typesArr['title'] = date("d-m-Y", strtotime($goal['start_date'])) . ' to ' . date("d-m-Y", strtotime($goal['end_date']));
                            }
                            $colorClass = "info";
                            if ($goal['achievements']['percent'] >= 100) {
                                $colorClass = "success";
                            } else if ($goal['achievements']['percent'] >= 90) {
                                $colorClass = "warning";
                            }
                            $getGoalsProgressBars = get_goals_progress_bars($goal['id']);
                        ?>
                            <div class="col-md-12">
                                <div class="goal">
                                    <h4 class="pull-left font-medium no-mtop">
                                        <?php echo $goal['goal_type_name']; ?>
                                        <br />
                                        <small><?php echo $goal['subject']; ?></small>
                                        <br />
                                        <small><?= $typesArr['type'] ?> - <?= $typesArr['title'] ?></small>
                                    </h4>
                                    <div class="clearfix"></div>
                                    <div class="progress-container">
                                        <?php
                                        foreach ($getGoalsProgressBars as $key => $item) {
                                        ?>
                                            <div class="progress-section">
                                                <div class="widget-goal-progress" data-progress-percent="<?= $item['achievements']['progress_bar_percent']; ?>" data-reverse="true">
                                                    <strong class="goal-percent"><?= $item['achievements']['percent']; ?>%</strong>
                                                </div>
                                                <div class="small-details">
                                                    <?= $item['achievements']['total']; ?> / <?= $item['target']; ?>
                                                </div>
                                                <div class="duration-title"><?= $item['title']; ?></div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>

                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="chat-footer" style="background:<?php echo $currentChatColor; ?>">
                    <div class="online">
                        <i onclick="goalCircleTransform();" data-toggle="tooltip" title="Goals" class="fa fa-trophy toCircle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= site_url('assets/plugins/jquery-circle-progress/circle-progress.min.js') ?>"></script>
    <script>
        var main_goal = $('#mainGoalId');
        var scrollPosition = main_goal.find('.scroll');

        $('.widget-goal-progress').each(function() {
            var percent = Number($(this).attr('data-progress-percent'));
            var color = "#059DC1";
            if(percent >= 1){
                color = "#02bf3e";
            } else if(percent >= 0.9){
                color = "#d18b00";
            }
            $(this).circleProgress({
                value: percent,
                size: 45,
                animation: false,
                fill: {
                    color: color
                }
            });
        })



        var goals_positions = JSON.parse(localStorage.goals_positions || "{}");
        $.each(goals_positions, function(id, pos) {
            $("#goalPopupView #" + id).css(pos);
        });
        setTimeout(function() {
            $('#mainGoalId').css('display', 'block');
        }, 200);
        $(function() {
            'use strict';

            if (localStorage.isToggled_goal == 'true') {
                main_goal.find('#membersContent').hide(function() {
                    setTimeout(function() {
                        main_goal.find('#membersContent').show();
                    }, 2000);
                });
            }
        });

        var availableWidth = document.body.clientWidth - 305;
        var availableHeight = document.body.clientHeight - 250;

        $("#goalPopupView .draggable").draggable({
            axis: "x,y",
            scroll: false,
            start: function(event, ui) {
                $('#mainChatId').addClass('main-chat-dragging isToggled');
            },
            drag: function(event, ui) {
                if (ui.position.left > 0) {
                    ui.position.left = 0;
                    goals_positions[this.id] = ui.position;
                }
                if (ui.position.left < -availableWidth) {
                    ui.position.left = -availableWidth;
                    goals_positions[this.id] = -availableWidth;
                }
                if (ui.position.top > 0) {
                    ui.position.top = 0;
                    goals_positions[this.id] = ui.position;
                }
                if (ui.position.top < -availableHeight) {
                    ui.position.top = -availableHeight;
                    goals_positions[this.id] = -availableHeight;
                }
                goals_positions[this.id] = ui.position;
                localStorage.goals_positions = JSON.stringify(goals_positions);
            }
        });

        window.addEventListener('load', function() {
            var localStoragePosGoal = localStorage.goal_head_position;
            var localStorageisToggledGoal = localStorage.isToggled_goal;
            if (localStorage.isToggled_goal == 'true') {
                goalCircleTransform();
            }
        });




        $(window).resize(function() {
            $('#goalPopupView .chatBoxWrap').css({
                'width': $(window).width() - main_goal.find('#membersContent').width() - 30
            });
            updateBoxPosition();
        });


        /*---------------* Track window resize activity, hides chat when in mobile version *---------------*/
        $(window).resize(function() {
            if ($(window).width() < 733) {
                $('#goalPopupView').hide();
            } else {
                $('#goalPopupView').show();
            }
        });
        if ($(window).width() < 733) {
            $('#goalPopupView').hide();
        } else {
            $('#goalPopupView').show();
        }

        function goalCircleTransform() {
            var main = $('#mainGoalId');
            var inputColor = main.find('.colorHolder');
            var inputcolorGradient = main.find('#colorGradientChanger');

            if (!main.hasClass('main-chat-dragging')) {
                main.find('.scroll, .chat-footer, .fa.fa-eercast, .chat-footer .online, .topInfo, #searchUsers, #disableSound, #colorChanger, #membersContent').toggleClass('isToggled');
                (inputColor.is(':visible')) ? inputColor.hide(): inputColor.show();
            }
            if (main.find('.scroll').hasClass('isToggled')) {
                inputcolorGradient.hide();
                localStorage.isToggled_goal = 'true';
                localStorage.goal_head_position = 'none';
                scrollPosition.css('display', 'none');
            } else {
                inputcolorGradient.show();
                scrollPosition.css('display', 'block');
                localStorage.goal_head_position = 'none';
                localStorage.isToggled_goal = 'false';
            }
        }
    </script>
<?php } ?>