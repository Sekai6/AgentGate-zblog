<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();

if (!$zbp->CheckPlugin('AgentGate')) {
    $zbp->ShowError(48);
    die();
}
if (!$zbp->CheckRights('admin')) {
    $zbp->ShowError(6);
    die();
}

$blogtitle = 'AgentGate ' . $zbp->lang['msg']['settings'];

if (count($_POST) > 0) {
    CheckIsRefererValid();
    $zbp->Config('AgentGate')->global_enabled     = (int)($_POST['global_enabled'] ?? 0);
    $zbp->Config('AgentGate')->cookie_expire      = (int)($_POST['cookie_expire'] ?? 3600);
    $zbp->Config('AgentGate')->whitelist          = trim($_POST['whitelist'] ?? '');
    $zbp->Config('AgentGate')->protected_articles = trim($_POST['protected_articles'] ?? '');
    $zbp->Config('AgentGate')->widget_url          = trim($_POST['widget_url'] ?? 'https://captcha.kisara.art');
    $zbp->SaveConfig('AgentGate');
    $zbp->SetHint('good');
    Redirect('./main.php');
}

require $zbp->path . 'zb_system/admin/admin_header.php';
require $zbp->path . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
    <div class="divHeader2"><?php echo $blogtitle; ?></div>
    <div class="SubMenu"></div>
    <div id="divMain2" style="background:#0a0a0a;color:#00ff41;font-family:monospace;padding:20px;border:1px solid #00ff41;">
        <form id="form1" name="form1" method="post" action="">
            <input type="hidden" name="csrfToken" value="<?php echo $zbp->GetCSRFToken(); ?>">

            <p style="font-size:14px;margin-bottom:20px;">
                [ AGENTGATE SETTINGS ]<br>
                <span style="color:#666;">Configure how the human detection system behaves.</span>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="global_enabled" value="1"
                        <?php echo ($zbp->Config('AgentGate')->global_enabled == 1) ? 'checked' : ''; ?>>
                    &#x5168;&#x5c40;&#x542f;&#x7528; &mdash; &#x6240;&#x6709;&#x9875;&#x9762;&#x9ed8;&#x8ba4;&#x5f00;&#x542f;&#x4eba;&#x7c7b;&#x62e6;&#x622a;
                </label>
            </p>

            <p>
                <label>
                    &#x53d7;&#x4fdd;&#x62a4;&#x6587;&#x7ae0; ID&#xff08;&#x82f1;&#x6587;&#x9017;&#x53f7;&#x5206;&#x9694;&#xff0c;&#x4e0d;&#x542f;&#x7528;&#x5168;&#x5c40;&#x65f6;&#x4ec5;&#x8fd9;&#x4e9b;&#x6587;&#x7ae0;&#x89e6;&#x53d1;&#x62e6;&#x622a;&#xff09;<br>
                    <input type="text" name="protected_articles" value="<?php echo htmlspecialchars($zbp->Config('AgentGate')->protected_articles ?? ''); ?>"
                        style="background:#000;border:1px solid #00ff41;color:#00ff41;font-family:monospace;padding:4px;width:400px;"
                        placeholder="&#x4f8b;&#x5982;: 231,45,102">
                </label>
            </p>

            <p>
                <label>
                    Cookie &#x6709;&#x6548;&#x671f;&#xff08;&#x79d2;&#xff09;<br>
                    <input type="number" name="cookie_expire" value="<?php echo (int)($zbp->Config('AgentGate')->cookie_expire ?? 3600); ?>"
                        style="background:#000;border:1px solid #00ff41;color:#00ff41;font-family:monospace;padding:4px;width:200px;">
                </label>
            </p>

            <p>
                <label>
                    &#x81ea;&#x5b9a;&#x4e49; UA &#x767d;&#x540d;&#x5355;&#xff08;&#x6bcf;&#x884c;&#x4e00;&#x4e2a;&#x5173;&#x952e;&#x8bcd;&#xff09;<br>
                    <textarea name="whitelist" rows="5" cols="40"
                        style="background:#000;border:1px solid #00ff41;color:#00ff41;font-family:monospace;padding:4px;"><?php echo htmlspecialchars($zbp->Config('AgentGate')->whitelist ?? ''); ?></textarea>
                </label>
            </p>

            <p style="border:1px solid #ff6600;padding:10px;background:rgba(255,102,0,0.08);color:#ff6600;font-size:12px;margin-bottom:16px;">
                &#x26a0; <strong>Demo Service Notice</strong><br>
                The default URL points to a public demo instance. This service may be discontinued at any time.<br>
                If verification stops working, please deploy your own instance and update the URL below.<br>
                Source: <a href="https://github.com/Artistkisa/AgentGate-captcha" style="color:#ff9933;" target="_blank">AgentGate-captcha</a>
            </p>

            <p>
                <label>
                    AgentGate Service URL<br>
                    <input type="text" name="widget_url" value="<?php echo htmlspecialchars($zbp->Config('AgentGate')->widget_url ?: 'https://captcha.kisara.art'); ?>"
                        style="background:#000;border:1px solid #00ff41;color:#00ff41;font-family:monospace;padding:4px;width:400px;"
                        placeholder="https://your-domain.com">
                    <span style="color:#666;font-size:11px;display:block;margin-top:4px;">
                        Default: https://captcha.kisara.art (public demo, may be discontinued)
                    </span>
                </label>
            </p>

            <p>
                <input type="submit" value="&#x4fdd;&#x5b58;&#x8bbe;&#x7f6e;"
                    style="background:#000;border:2px solid #00ff41;color:#00ff41;font-family:monospace;padding:8px 16px;cursor:pointer;">
            </p>
        </form>
    </div>
</div>
<?php
require $zbp->path . 'zb_system/admin/admin_footer.php';
?>
