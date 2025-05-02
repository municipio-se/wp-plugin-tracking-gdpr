/*
A promise that resolves when the document is fully loaded.
*/
export const documentLoadComplete = new Promise((resolve) => {
  if (document.readyState === 'complete') {
    resolve(window);
  } else {
    document.addEventListener('readystatechange', () => {
      if (document.readyState === 'complete') {
        resolve(window);
      }
    });
  }
});
