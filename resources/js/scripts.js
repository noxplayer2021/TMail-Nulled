(function() {
  //Copy the Email ID Function
  document.getElementById('btn_copy') &&
    document.getElementById('btn_copy').addEventListener('click', () => {
      let email = document.getElementById('email_id');
      if (email) {
        let el = document.createElement('input');
        el.type = 'text';
        el.value = email.innerText;
        document.body.appendChild(el);
        var isiOSDevice = navigator.userAgent.match(/ipad|iphone/i);
        if (isiOSDevice) {
          var editable = el.contentEditable;
          var readOnly = el.readOnly;
          el.contentEditable = true;
          el.readOnly = false;
          var range = document.createRange();
          range.selectNodeContents(el);
          var selection = window.getSelection();
          selection.removeAllRanges();
          selection.addRange(range);
          el.setSelectionRange(0, 999999);
          el.contentEditable = editable;
          el.readOnly = readOnly;
        } else {
          el.select();
        }
        document.execCommand('copy');
        el.remove();
        const event = new CustomEvent('showAlert', {
          bubbles: true,
          detail: {
            type: 'success',
            message: document.querySelector('.language-helper .copy_text')
              .innerText,
          },
        });
        document.getElementById('btn_copy').dispatchEvent(event);
      }
    });

  //Supporting Code for "Copy of Email ID" to remove the selection
  document.getElementById('email_id') &&
    document.getElementById('email_id').addEventListener('click', () => {
      document.getElementById('email_id').disabled = true;
    });

  //Scrolling to Div
  document.querySelector('.messages') &&
    document.querySelector('.messages').addEventListener('click', () => {
      setTimeout(() => {
        scroll({
          top: document.querySelector('.message-content').offsetTop,
          behavior: 'smooth',
        });
      }, 100);
    });

  //Cookie Policy
  document.getElementById('cookie') &&
    document.addEventListener('DOMContentLoaded', () => {
      if (!localStorage.getItem('cookie')) {
        document.getElementById('cookie').classList.remove('hidden');
        document.getElementById('cookie').classList.add('flex');
      }
    });

  //Cookie Policy Close
  document.getElementById('cookie_close') &&
    document.getElementById('cookie_close').addEventListener('click', () => {
      localStorage.setItem('cookie', 'closed');
      document.getElementById('cookie').classList.add('hidden');
      document.getElementById('cookie').classList.remove('flex');
    });

  //Locale Update
  document.getElementById('locale') &&
    document.getElementById('locale').addEventListener('change', (e) => {
      const form = document.getElementById('locale-form');
      form.action = form.action + `/${e.target.value}`;
      form.submit();
    });

  //Locale Update
  document.getElementById('locale-mobile') &&
    document.getElementById('locale-mobile').addEventListener('change', (e) => {
      const form = document.getElementById('locale-form-mobile');
      form.action = form.action + `/${e.target.value}`;
      form.submit();
    });

  //Download Email
  document.addEventListener('loadDownload', () => {
    if (document.querySelector('.download')) {
      document.querySelectorAll('.download').forEach((el) => {
        el.addEventListener('click', (e) => {
          e.preventDefault();
          const a = document.createElement('a');
          a.download = `email-${e.target.dataset.id}.eml`;
          a.href = makeTextFile(e.target.dataset.id);
          document.body.appendChild(a);
          a.click();
          a.remove();
        });
      });
    }
  });

  function makeTextFile(id) {
    var textFile = null;
    text = document.querySelector(`#message-${id} textarea`).value;
    var data = new Blob([text], { type: 'text/plain' });
    if (textFile !== null) {
      window.URL.revokeObjectURL(textFile);
    }
    textFile = window.URL.createObjectURL(data);
    return textFile;
  }

  /** Shortcode Handler for [blogs] */
  if (typeof Shortcode !== 'undefined') {
    new Shortcode(document.querySelector('.page'), {
      blogs: function() {
        var data = '<div class="grid grid-cols-6 gap-6">';
        var fetchUrl =
          this.options.url +
          '/wp-json/wp/v2/posts?_fields[]=link&_fields[]=title&_fields[]=excerpt';
        var filters = {
          context: this.options.context,
          page: this.options.page,
          per_page: this.options.per_page,
          search: this.options.search,
          after: this.options.after,
          author: this.options.author,
          author_exclude: this.options.author_exclude,
          before: this.options.before,
          exclude: this.options.exclude,
          include: this.options.include,
          offset: this.options.offset,
          order: this.options.order,
          orderby: this.options.orderby,
          slug: this.options.slug,
          status: this.options.status,
          categories: this.options.categories,
          categories_exclude: this.options.categories_exclude,
          tags: this.options.tags,
          tags_exclude: this.options.tags_exclude,
          sticky: this.options.sticky,
        };
        Object.keys(filters).forEach(function(key) {
          if (filters[key]) {
            fetchUrl += '&' + key + '=' + filters[key];
          }
        });
        fetch(fetchUrl)
          .then((response) => response.json())
          .then((blogs) => {
            blogs.forEach(function(item) {
              data +=
                '<div class="col-span-6 md:col-span-2 px-5 py-4 bg-gray-100 rounded-md">';
              data += '<a href="' + item.link + '" target="_blank">';
              data +=
                '<span class="no-underline">' + item.title.rendered + '</span>';
              data +=
                '<span class="text-xs">' + item.excerpt.rendered + '</span>';
              data += '</a>';
              data += '</div>';
            });
            data += '</div>';
            if (blogs.length) {
              document.getElementById('blogs').innerHTML = data;
            } else {
              document.getElementById('blogs').innerHTML =
                '<div class="text-center">204 - NO CONTENT AVAILABLE</div>';
            }
          });
        return `<div id='blogs'><div class="grid grid-cols-6 gap-6"><div class="col-span-6 bg-gray-100 rounded-md px-5 py-4 text-center"><i class="fas fa-sync-alt fa-spin"></i></div></div></div>`;
      },
      html: function() {
        let txt = document.createElement('textarea');
        txt.innerHTML = this.contents;
        return txt.value;
      },
    });
  }
})();
