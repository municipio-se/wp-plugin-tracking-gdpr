/**
 * Shows a consent dialog before loading an iframe. Integrates with the Vanilla
 * CookieConsent library. The iframe is only loaded if the user accepts the
 * cookies.
 *
 * @customElement wstg-iframe
 * @description A custom element for embedding iframes with tracking.
 */

import { documentLoadComplete } from './load';

const IFRAME_ATTRIBUTE_NAMES = [
  'allow',
  'allowfullscreen',
  'height',
  'loading',
  'name',
  'referrerpolicy',
  'sandbox',
  'src',
  'srcdoc',
  'width',
];

/*
Example:
<wstg-iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" service="YouTube" category="embedded">
  <img src="https://placehold.co/1920x1080" slot="thumbnail">
  <dialog slot="dialog" open>
    <p>This content is provided by a third party. By viewing the external content, you agree to the <a rel="noreferrer noopener" href="https://www.youtube.com/t/terms" target="_blank">terms of service</a> for YouTube.</p>
    <button slot="acceptButton">Accept</button>
    <button slot="settingsButton">Cookie settings</button>
  </dialog>
</wstg-iframe>
*/

class WstgIframeElement extends HTMLElement {
  protected _internals: ElementInternals;
  public iframe: HTMLIFrameElement | null = null;
  protected iframeAttributes?: Record<string, string>;

  constructor() {
    super();
    this._internals = this.attachInternals();
    this.accept = this.accept.bind(this);
    this.showSettings = this.showSettings.bind(this);
  }

  get loader() {
    return this.querySelector('[slot="loader"]') as HTMLDivElement | null;
  }

  get dialog() {
    return this.querySelector('[slot="dialog"]') as
      | HTMLDialogElement
      | HTMLElement
      | null;
  }

  get acceptButton() {
    return this.querySelector(
      '[slot="acceptButton"]',
    ) as HTMLButtonElement | null;
  }

  get settingsButton() {
    return this.querySelector(
      '[slot="settingsButton"]',
    ) as HTMLButtonElement | null;
  }

  get service() {
    return this.getAttribute('service') || null;
  }

  get resolvedService(): string | null {
    const service = this.service ?? 'undefined';
    const config = window.CookieConsent.getConfig();
    for (const category of Object.values(config.categories)) {
      if (category.services && service in category.services) {
        return service;
      }
    }
    return null;
  }

  get category() {
    return this.getAttribute('category') || 'embedded';
  }

  connectedCallback() {
    this.acceptButton?.addEventListener('click', this.accept);
    this.settingsButton?.addEventListener('click', this.showSettings);
    // Merge attributes from this element and its "iframe" slot element
    this.iframeAttributes = {
      ...Object.fromEntries(
        Array.from(this.attributes)
          .filter(
            (attr) =>
              attr.name.startsWith('iframe-') ||
              IFRAME_ATTRIBUTE_NAMES.includes(attr.name),
          )
          .map((attr) => [attr.name.replace(/^iframe-/, ''), attr.value]),
      ),
      ...Object.fromEntries(
        Array.from(this.querySelector('[slot="iframe"]')?.attributes || [])
          .filter((attr) => !['hidden', 'slot'].includes(attr.name))
          .map((attr) => [attr.name, attr.value]),
      ),
    };

    // Remove the "iframe" slot element from the DOM
    this.querySelector('[slot="iframe"]')?.remove();
    documentLoadComplete.then(() => {
      if (!this.isConnected) {
        return;
      }
      this.handleLoad();
    });
  }

  handleLoad() {
    this.loader?.setAttribute('hidden', '');
    window.addEventListener('cc:onFirstConsent', () => {
      this.update();
    });
    window.addEventListener('cc:onChange', () => {
      this.update();
    });
    this.update();
  }

  get accepted() {
    if (this.resolvedService) {
      return window.CookieConsent.acceptedService(
        this.resolvedService,
        this.category,
      );
    }
    return window.CookieConsent.acceptedCategory(this.category);
  }

  update() {
    if (this.accepted) {
      if (this.dialog instanceof HTMLDialogElement) {
        this.dialog!.close();
      } else {
        this.dialog!.setAttribute('hidden', '');
      }
      this.loadIframe();
    } else {
      if (this.dialog instanceof HTMLDialogElement) {
        this.dialog!.showModal();
      } else {
        this.dialog!.removeAttribute('hidden');
      }
      this.unloadIframe();
    }
  }

  accept() {
    if (this.dialog instanceof HTMLDialogElement) {
      this.dialog!.close();
    } else {
      this.dialog!.setAttribute('hidden', '');
    }
    if (this.service) {
      let acceptedServices = new Set(
        window.CookieConsent.getCookie().services[this.category] || [],
      );
      acceptedServices.add(this.service);
      window.CookieConsent.acceptService(
        Array.from(acceptedServices),
        this.category,
      );
    } else {
      window.CookieConsent.acceptCategory(this.category);
    }
  }

  showSettings() {
    window.CookieConsent.showPreferences();
  }

  disconnectedCallback() {
    this.acceptButton?.removeEventListener('click', this.accept);
    this.settingsButton?.removeEventListener('click', this.showSettings);
  }

  loadIframe() {
    if (this.iframe) {
      return;
    }
    this.iframe = document.createElement('iframe');
    if (this.iframeAttributes) {
      for (const [name, value] of Object.entries(this.iframeAttributes)) {
        this.iframe.setAttribute(name, value);
      }
    }
    this.appendChild(this.iframe);
  }

  unloadIframe() {
    if (!this.iframe) {
      return;
    }
    this.iframe.remove();
    this.iframe = null;
  }
}

customElements.define('wstg-iframe', WstgIframeElement);
