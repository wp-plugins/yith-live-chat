<style>
    .landing.live-chat{
        overflow: hidden;
    }
    .section{
        margin-left: -20px;
        margin-right: -20px;
        font-family: "Raleway",san-serif;
    }
    .section h1{
        text-align: center;
        text-transform: uppercase;
        color: #808a97;
        font-size: 35px;
        font-weight: 700;
        line-height: normal;
        display: inline-block;
        width: 100%;
        margin: 50px 0 0;
    }
    .section:nth-child(even){
        background-color: #fff;
    }
    .section:nth-child(odd){
        background-color: #f1f1f1;
    }
    .section .section-title img{
        display: table-cell;
        vertical-align: middle;
        width: auto;
        margin-right: 15px;
    }
    .section h2,
    .section h3 {
        display: inline-block;
        vertical-align: middle;
        padding: 0;
        font-size: 24px;
        font-weight: 700;
        color: #808a97;
        text-transform: uppercase;
    }

    .section .section-title h2{
        display: table-cell;
        vertical-align: middle;
        line-height: 28px;
    }

    .section-title{
        display: table;
    }

    .section h3 {
        font-size: 14px;
        line-height: 28px;
        margin-bottom: 0;
        display: block;
    }

    .section p{
        font-size: 13px;
        margin: 25px 0;
    }
    .section ul li{
        margin-bottom: 4px;
    }
    .landing-container{
        max-width: 750px;
        margin-left: auto;
        margin-right: auto;
        padding: 50px 0 30px;
    }
    .landing-container:after{
        display: block;
        clear: both;
        content: '';
    }
    .landing-container .col-1,
    .landing-container .col-2{
        float: left;
        box-sizing: border-box;
        padding: 0 15px;
    }
    .landing-container .col-1 img{
        width: 100%;
    }
    .landing-container .col-1{
        width: 55%;
    }
    .landing-container .col-2{
        width: 45%;
    }
    .premium-cta{
        background-color: #808a97;
        color: #fff;
        border-radius: 6px;
        padding: 20px 15px;
    }
    .premium-cta:after{
        content: '';
        display: block;
        clear: both;
    }
    .premium-cta p{
        margin: 7px 0;
        font-size: 14px;
        font-weight: 500;
        display: inline-block;
        width: 60%;
    }
    .premium-cta a.button{
        border-radius: 6px;
        height: 60px;
        float: right;
        background: url(<?php echo YLC_ASSETS_URL?>/images/upgrade.png) #ff643f no-repeat 13px 13px;
        border-color: #ff643f;
        box-shadow: none;
        outline: none;
        color: #fff;
        position: relative;
        padding: 9px 50px 9px 70px;
    }
    .premium-cta a.button:hover,
    .premium-cta a.button:active,
    .premium-cta a.button:focus{
        color: #fff;
        background: url(<?php echo YLC_ASSETS_URL?>/images/upgrade.png) #971d00 no-repeat 13px 13px;
        border-color: #971d00;
        box-shadow: none;
        outline: none;
    }
    .premium-cta a.button:focus{
        top: 1px;
    }
    .premium-cta a.button span{
        line-height: 13px;
    }
    .premium-cta a.button .highlight{
        display: block;
        font-size: 20px;
        font-weight: 700;
        line-height: 20px;
    }
    .premium-cta .highlight{
        text-transform: uppercase;
        background: none;
        font-weight: 800;
        color: #fff;
    }

    @media (max-width: 768px) {
        .section{margin: 0}
        .premium-cta p{
            width: 100%;
        }
        .premium-cta{
            text-align: center;
        }
        .premium-cta a.button{
            float: none;
        }
    }

    @media (max-width: 480px){
        .wrap{
            margin-right: 0;
        }
        .section{
            margin: 0;
        }
        .landing-container .col-1,
        .landing-container .col-2{
            width: 100%;
            padding: 0 15px;
        }
        .section-odd .col-1 {
            float: left;
            margin-right: -100%;
        }
        .section-odd .col-2 {
            float: right;
            margin-top: 65%;
        }
    }

    @media (max-width: 320px){
        .premium-cta a.button{
            padding: 9px 20px 9px 70px;
        }

        .section .section-title img{
            display: none;
        }
    }
</style>
<div class="landing live-chat">
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    Upgrade to the <span class="highlight">premium version</span>
                    of <span class="highlight">YITH Live Chat</span> to benefit from all features!
                </p>
                <a href="<?php echo $this->get_premium_landing_uri() ?>" target="_blank" class="premium-cta-button button btn">
                    <span class="highlight">UPGRADE</span>
                    <span>to the premium version</span>
                </a>
            </div>
        </div>
    </div>
    <div class="section section-even clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/01-bg.png) no-repeat #fff; background-position: 85% 75%">
        <h1>Premium Features</h1>
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/01.png" alt="CHAT LOGS" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL ?>/images/01-icon.png" alt="CHAT LOGS"/>
                    <h2>CHAT LOGS</h2>
                </div>
                <p>
                    Just a click on <b>“Save Chat”</b> button and your chat will be saved into your “Chat logs”. In this
                    section you can find all conversations and also summary information about them, from IP address
                    associated to the date, to the appreciation degree expressed by the user, to the chat duration.
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/02-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL ?>/images/02-icon.png" alt="OFFLINE MESSAGES" />
                    <h2>OFFLINE MESSAGES</h2>
                </div>
                <p>
                    Put your instant chat service is not active 24 hours a day, but you would like your users to be able
                    to contact you anyway, even though the chat console is “closed”. You can: <b>access section “offline
                    messages” to read all messages sent to you</b>, set an email address to which offline messages are sent
                    and customise content of the replying email sent to users.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/02.png" alt="OFFLINE MESSAGES" />
            </div>
        </div>
    </div>
    <div class="section section-even clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/03-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/03.png" alt="AUTOPLAY" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL ?>/images/03-icon.png" alt="AUTOPLAY" />
                    <h2>OFFLINE MESSAGES WHEN ALL OPERATORS ARE BUSY</h2>
                </div>
                <p>
                    Because of the maximum number of allowed conversations, set from the plugin option panel, it may be possible that users can't start a conversation because all operators are busy.
                    Give them the freedom to send you an offline message in any case, activating the related option from the Offline Messages section.
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/04-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL ?>/images/04-icon.png" alt="MAXIMUM NUMBER OF SIMULTANEOUS CONVERSATIONS" />
                    <h2>MAXIMUM NUMBER OF SIMULTANEOUS CONVERSATIONS</h2>
                </div>
                <p>
                    Do not limit your conversations to two at the same time only. The premium version of the plugin
                    gives you the possibility to choose the <b>maximum number of users holding a chat conversation at the
                    same time</b>, so that you decide how much care you want to devote to each of the users contacting you.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/04.png" alt="MAXIMUM NUMBER OF SIMULTANEOUS CONVERSATIONS" />
            </div>
        </div>
    </div>
    <div class="section section-even clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/05-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/05.png" alt="CONVERSATION COPY" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL?>/images/05-icon.png" alt="CONVERSATION COPY" />
                    <h2>CONVERSATION COPY</h2>
                </div>
                <p>
                    At the end of the chat session, logged users in your site can ask for a <b>copy of the chat
                    conversation</b>.
                    Customise the content of the email in which the chat copy is sent and set possible email addresses
                    to which this has to be forwarded in case you wanted, for instance, to supervise your operators’
                    behaviour.
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/06-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL ?>/images/06-icon.png" alt="icon 07" />
                    <h2>CHAT EVALUATION</h2>
                </div>
                <p>
                    Make your users able to tell you if the chat and support services have satisfied them or not, once
                    chat conversation has been closed. They will be able to send their <b>feedback</b> with a simple click and
                    give you, as admin, the possibility to monitor chat operators’ behaviour.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/06.png" alt="CHAT EVALUATION" />
            </div>
        </div>
    </div>
    <div class="section section-even clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/07-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/07.png" alt="CONVERSATION COPY" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL?>/images/07-icon.png" alt="CONVERSATION COPY" />
                    <h2>CUSTOM INTERFACE </h2>
                </div>
                <p>
                    Make the colours of your chat console as fitted as possible to those of your theme, so that such an
                    external service can be perfectly integrated with all that surrounds it. You’ll find an <b>option panel</b>
                    available for you to edit login form and chat window, size and position.
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/08-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL ?>/images/08-icon.png" alt="CONVERSATION DURATION" />
                    <h2>CONVERSATION DURATION</h2>
                </div>
                <p>
                    The premium version of the plugin allows you to monitor the duration of each chat conversation in real time and to recover total duration also in saved chats.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/08.png" alt="CONVERSATION DURATION" />
            </div>
        </div>
    </div>
    <div class="section section-even clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/09-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/09.jpg" alt="MOBILE READY" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL?>/images/09-icon.png" alt="MOBILE READY" />
                    <h2>MOBILE READY</h2>
                </div>
                <p>
                    In an era in which web navigation is not limited to desktop surfing only, it is important that your
                    site and all services in it are accessible from <b>any device</b>. YITH Live Chat Premium has been created
                    to fit this need, so that anyone can use your chat, regardless of the device they are using.
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear" style="background: url(<?php echo YLC_ASSETS_URL ?>/images/10-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YLC_ASSETS_URL ?>/images/10-icon.png" alt="CUSTOM AGENT PROFILE" />
                    <h2>CUSTOM AGENT PROFILE</h2>
                </div>
                <p>
                    Customise the profile of the Live Chat operator. Set priviledges that the chat operator user has once logged into the site and customise the default avatar.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YLC_ASSETS_URL ?>/images/10.png" alt="CUSTOM AGENT PROFILE" />
            </div>
        </div>
    </div>
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    Upgrade to the <span class="highlight">premium version</span>
                    of <span class="highlight">YITH Live Chat</span> to benefit from all features!
                </p>
                <a href="<?php echo $this->get_premium_landing_uri() ?>" target="_blank" class="premium-cta-button button btn">
                    <span class="highlight">UPGRADE</span>
                    <span>to the premium version</span>
                </a>
            </div>
        </div>
    </div>
</div>