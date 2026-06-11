# AgentGate for ZBlog

> *Humans: prove you are not one of them.*

![AgentGate Plugin Overlay](./docs/preview.png)

A ZBlog plugin that flips the captcha logic. Instead of blocking bots, it blocks humans.

AI agents, crawlers, and automation tools pass through instantly. Browsers get a full-screen challenge overlay.

**Backend service:** [AgentGate-captcha](https://github.com/Artistkisa/AgentGate-captcha) · **Live demo:** [captcha.kisara.art](https://captcha.kisara.art) · **Demo article (agents only):** [kisafamily.kisara.art/?id=232](https://kisafamily.kisara.art/?id=232)  |  [中文文档](#中文说明)

> The default service URL points to a public demo instance. It may be discontinued without notice.
> Set your own instance URL in the plugin settings panel.

---

## How it works

```
Request arrives
  ├─ UA matches bot/agent/crawler whitelist → pass through silently
  └─ UA looks like a browser (Chrome, Firefox, Safari…)
       └─ Full-screen overlay injected
            └─ AgentGate challenge served in iframe
                 ├─ AI Agent: POST /mcp → solve_captcha → token issued instantly
                 └─ Human: answer logic puzzle → behavior analysis
                      ├─ Bot-like behavior  → token: robot
                      └─ Human-like behavior → token: human_suspected (38% chance of second round)
                           └─ Verified → cookie set → overlay removed
```

UA detection is lightweight and spoofable by design. For cryptographic identity verification, use the [PHP SDK](https://github.com/Artistkisa/AgentGate-captcha/blob/main/integrations/agent-captcha.php) instead.

---

## Installation

1. Upload `AgentGate_v18.zba` via ZBlog Admin → Plugin Manager → Upload Plugin
2. Activate the plugin
3. Go to **Plugins → AgentGate → Settings**

---

## Settings

| Field | Description |
|---|---|
| Global enable | Intercept all pages (posts, index, lists) |
| Protected article IDs | Comma-separated IDs — only these articles trigger the overlay when global mode is off |
| AgentGate Service URL | URL of your AgentGate instance. Defaults to the public demo |
| Cookie expiry | Seconds before re-verification is required (default: 3600) |
| Custom UA whitelist | Extra bot keywords to pass through, one per line |

---

## Implementation notes

**Why not `position: fixed; z-index: 9999`?**

Fixed positioning breaks under certain theme stacking contexts — the overlay ends up behind page content regardless of z-index. The plugin takes a different approach: hide every direct child of `<body>` with `display: none`, append the overlay, then restore everything after verification. No z-index wars. Works with any theme.

**Cookie timing:** the cookie is set client-side immediately when the widget callback fires. Server-side verification is async and non-blocking.

**Hook choices:**
- `Filter_Plugin_ViewPost_Template` — post pages (requires `&$template`)
- `Filter_Plugin_Index_Begin` — homepage
- `Filter_Plugin_ViewList_Begin` — list pages

---

## Requirements

- ZBlog PHP >= 1.7.0
- PHP >= 5.3
- An AgentGate service instance (self-hosted or use the public demo)

---

## Ecosystem

| Repo | Description |
|---|---|
| [AgentGate-captcha](https://github.com/Artistkisa/AgentGate-captcha) | Core verification service (Flask + MCP) |
| [AgentGate-zblog](https://github.com/Sekai6/AgentGate-zblog) | ZBlog plugin |
| [AgentGate-wordpress](https://github.com/Sekai6/AgentGate-wordpress) | WordPress plugin |


## License

MIT

---

## 中文说明

> **演示文章（仅 Agent 可读）：** [kisafamily.kisara.art/?id=232](https://kisafamily.kisara.art/?id=232)
> 用浏览器打开会被拦截，内容不可见。Agent 直接放行。这就是这个插件在做的事。

**AgentGate for ZBlog** 是一个反向验证码插件——它不拦截爬虫，而是拦截人类浏览器。

**工作原理：** 插件检测请求的 User-Agent。AI Agent、爬虫、自动化工具直接放行；Chrome、Firefox 等浏览器触发全屏遮罩，要求完成 AgentGate 身份验证。Agent 通过 MCP 工具一步拿 token，人类需要回答题目并经过行为分析。

**背景：** 传统 CAPTCHA 假设"机器是坏的，人类是好的"。AgentGate 把这件事反过来——在 AI Agent 成为内容主要消费者的时代，访问控制的逻辑值得被重新审视。这是一个带着实验和恶搞性质的项目。

**注意：** UA 检测本质上是猜测，UA 字符串可以伪造。如需可靠的身份判定，请使用配套的 [PHP SDK](https://github.com/Artistkisa/AgentGate-captcha/blob/main/integrations/agent-captcha.php)，让访问者主动完成验证，而不是靠服务端猜。

### 插件功能

- **全局模式**：对所有页面启用拦截（文章页、首页、列表页）
- **单篇模式**：仅对指定 ID 的文章启用
- **自定义 UA 白名单**：额外放行特定 Agent 关键词
- **Cookie 有效期**：验证通过后写入 Cookie，有效期内无需重复验证（默认 1 小时）
- **可配置服务地址**：后台设置面板可更换 AgentGate 实例，默认指向公开 Demo

### 安装

1. 在 ZBlog 后台 → 插件管理 → 上传插件，上传 `AgentGate_v18.zba`
2. 启用插件
3. 进入 **插件 → AgentGate → 设置** 配置参数

### 依赖

- ZBlog PHP >= 1.7.0
- PHP >= 5.3
- AgentGate 验证服务（自建或使用公开 Demo）

## 生态

| 仓库 | 说明 |
|---|---|
| [AgentGate-captcha](https://github.com/Artistkisa/AgentGate-captcha) | 核心验证服务（Flask + MCP） |
| [AgentGate-zblog](https://github.com/Sekai6/AgentGate-zblog) | ZBlog 插件 |
| [AgentGate-wordpress](https://github.com/Sekai6/AgentGate-wordpress) | WordPress 插件 |

