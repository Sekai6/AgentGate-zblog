<?php
RegisterPlugin('AgentGate', 'ActivePlugin_AgentGate');

function ActivePlugin_AgentGate() {
    Add_Filter_Plugin('Filter_Plugin_ViewPost_Template',    'AgentGate_CheckUA');
    Add_Filter_Plugin('Filter_Plugin_Index_Begin',          'AgentGate_CheckUA_Global');
    Add_Filter_Plugin('Filter_Plugin_ViewList_Begin',       'AgentGate_CheckUA_Global');
}

function InstallPlugin_AgentGate() {
    global $zbp;
    if (!$zbp->HasConfig('AgentGate')) {
        $zbp->Config('AgentGate')->global_enabled = 0;
        $zbp->Config('AgentGate')->cookie_expire  = 3600;
        $zbp->Config('AgentGate')->whitelist      = '';
        $zbp->SaveConfig('AgentGate');
    }
}

function UninstallPlugin_AgentGate() {
    global $zbp;
    $zbp->DelConfig('AgentGate');
}

// 防止重复输出
global $agentgate_injected;
$agentgate_injected = false;

// Read enabled state from plugin config
function AgentGate_IsArticleEnabled($id) {
    global $zbp;
    $list = $zbp->Config('AgentGate')->protected_articles;
    if (empty($list)) return false;
    $ids = array_map('trim', explode(',', $list));
    return in_array((string)$id, $ids, true);
}

// 文章详情页拦截（单篇 + 全局）
function AgentGate_CheckUA(&$template) {
    global $zbp, $agentgate_injected;
    if ($agentgate_injected) return;

    // Try multiple ways to get the current article
    $article = null;
    if (method_exists($template, 'GetTags')) {
        $article = $template->GetTags('article');
    }
    if (empty($article) && isset($template->tags['article'])) {
        $article = $template->tags['article'];
    }
    if (empty($article) && !empty($zbp->readpost)) {
        $article = $zbp->readpost;
    }
    if (empty($article) && !empty($zbp->article)) {
        $article = $zbp->article;
    }

    if (!$article || !is_object($article) || !$article->ID) {
        // Fallback: get article ID from URL
        $id = 0;
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
        } elseif (preg_match('/\/(\d+)\.html/', $_SERVER['REQUEST_URI'], $m)) {
            $id = (int)$m[1];
        }
        if ($id > 0) {
            $enabled = AgentGate_IsArticleEnabled($id);
            $global  = $zbp->Config('AgentGate')->global_enabled;
            if (!$enabled && $global != 1) return;
            if (!empty($_COOKIE['agentgate_verified']) && $_COOKIE['agentgate_verified'] === '1') return;
            if (!AgentGate_IsHuman()) return;
            $agentgate_injected = true;
            $zbp->footer .= AgentGate_OverlayHtml();
        }
        return;
    }

    $enabled = AgentGate_IsArticleEnabled($article->ID);
    $global  = $zbp->Config('AgentGate')->global_enabled;

    if (!$enabled && $global != 1) return;

    if (!empty($_COOKIE['agentgate_verified'])
        && $_COOKIE['agentgate_verified'] === '1') return;

    if (!AgentGate_IsHuman()) return;

    $agentgate_injected = true;
    $zbp->footer .= AgentGate_OverlayHtml();
}

// 首页/列表页拦截（仅全局模式）
function AgentGate_CheckUA_Global() {
    global $zbp, $agentgate_injected;
    if ($agentgate_injected) return;

    $global = $zbp->Config('AgentGate')->global_enabled;
    if ($global != 1) return;

    if (!empty($_COOKIE['agentgate_verified'])
        && $_COOKIE['agentgate_verified'] === '1') return;

    if (!AgentGate_IsHuman()) return;

    $agentgate_injected = true;
    $zbp->footer .= AgentGate_OverlayHtml();
}

function AgentGate_IsHuman() {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if (empty($ua)) return false;

    global $zbp;
    $whitelist = [
        'GPTBot','ChatGPT','ClaudeBot','anthropic-ai',
        'Googlebot','bingbot','Baiduspider','Sogou',
        'curl','wget','python-requests','Python-urllib',
        'Go-http-client','Java/','libwww-perl',
        'Scrapy','axios','node-fetch',
        'G.H.O.S.T','GHOST',
    ];
    foreach ($whitelist as $bot) {
        if (stripos($ua, $bot) !== false) return false;
    }

    $custom = $zbp->Config('AgentGate')->whitelist;
    if (!empty($custom)) {
        foreach (explode("\n", $custom) as $line) {
            $line = trim($line);
            if ($line && stripos($ua, $line) !== false) return false;
        }
    }

    $human = ['Mozilla','Chrome','Safari','Firefox','Edge','Opera','Trident','MSIE'];
    foreach ($human as $h) {
        if (stripos($ua, $h) !== false) return true;
    }

    return false;
}

function AgentGate_OverlayHtml() {
    global $zbp;
    $ajax_url = $zbp->host . 'zb_system/cmd.php?act=agentgate_verify';

    return <<<HTML
<style>
#agentgate-overlay{min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Courier New',Courier,monospace;background:#000}
#agentgate-overlay .agentgate-box{text-align:center;max-width:480px;padding:20px}
#agentgate-overlay pre{color:#00ff41;font-size:13px;text-align:left;line-height:1.5;margin:0 0 16px}
#agentgate-overlay #agent-captcha{margin:10px auto;min-height:80px}
#agentgate-overlay #agentgate-msg{color:#666;font-size:12px;margin-top:12px;min-height:18px}
</style>
<div id="agentgate-overlay">
  <div class="agentgate-box">
    <pre>
[ HUMAN PRESENCE DETECTED ]

Your User-Agent has been flagged as biological.
Access to this page requires identity verification.

Prove you are not human to continue.
    </pre>
    <div id="agent-captcha"></div>
    <div id="agentgate-msg"></div>
  </div>
</div>
<script>
(function(){
    var overlay=document.getElementById('agentgate-overlay');
    if(!overlay)return;
    var children=document.body.children;
    var hidden=[];
    for(var i=0;i<children.length;i++){
        var el=children[i];
        if(el.id==='agentgate-overlay')continue;
        if(el.tagName==='SCRIPT'||el.tagName==='STYLE'||el.tagName==='LINK')continue;
        el.style.display='none';
        hidden.push(el);
    }
    document.body.appendChild(overlay);
    document.body.style.background='#000';
    function showPage(){
        for(var i=0;i<hidden.length;i++){hidden[i].style.display='';}
        document.body.style.background='';
        if(overlay&&overlay.parentNode)overlay.parentNode.removeChild(overlay);
    }
    window.onAgentVerified=function(token){
        var msg=document.getElementById('agentgate-msg');
        if(msg){msg.style.color='#00ff41';msg.textContent='\u2713 Identity confirmed. Welcome, non-human.';}
        document.cookie='agentgate_verified=1;path=/;max-age=3600;SameSite=Lax';
        setTimeout(showPage,1000);
        var u='{$ajax_url}';
        if(u){fetch(u,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'token='+encodeURIComponent(token)}).catch(function(){});}
    };
})();
</script>
<script src="https://your-domain.com/static/widget.js"
        data-sitekey="universal"
        data-target="#agent-captcha"
        data-callback="onAgentVerified"
        data-cfasync="false"></script>
HTML;
}
