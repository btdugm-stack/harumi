// Entry point Worker Harumi.
// Routing: / dan /api/orders ditangani Worker; sisanya (css/js/img) disajikan dari aset statis.
import { renderIndex } from './template.js';
import { handleOrder } from './orders.js';

export default {
  async fetch(request, env) {
    const url = new URL(request.url);

    if (url.pathname === '/api/orders') {
      return handleOrder(request, env);
    }

    if (url.pathname === '/' || url.pathname === '/index.html') {
      return new Response(renderIndex(), {
        headers: { 'Content-Type': 'text/html; charset=utf-8' },
      });
    }

    // Fallback: aset statis (public/) atau 404 dari binding ASSETS.
    return env.ASSETS.fetch(request);
  },
};
