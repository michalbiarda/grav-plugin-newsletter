---
title: PLUGIN_NEWSLETTER.SUBSCRIBE_TO_NEWSLETTER

forms:
  subscribe:
    action:
    method: post
    refresh_prevention: true
    fields:
      - name: email
        type: email
        label: PLUGIN_NEWSLETTER.EMAIL
        validate:
          required: true
    buttons:
      - type: submit
        value: PLUGIN_NEWSLETTER.SUBSCRIBE
    process:
      - subscribe: []
---