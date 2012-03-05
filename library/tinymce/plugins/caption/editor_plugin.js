(function() {
        tinymce.create('tinymce.plugins.CaptionPlugin', {
                init : function(ed, url) {
                        ed.addCommand('mceCaption', function() {
                                var img = ed.selection.getNode();
                                if (img.nodeName != 'IMG') return;
                                var imgAlt = img.alt;
                                if (imgAlt == undefined) return;
                                var caption = document.createElement('div');
                                caption.innerHTML = '<small>' + imgAlt + '</small>';
                                img.parentNode.insertBefore(caption, img.nextSibling);
                        });

                        ed.addButton('caption', {
                                title : 'caption',
                                cmd : 'mceCaption',
                                //image : 'data:image/gif;base64,R0lGODlhFAAUAOMKAAAAADMzMyVjAJw4KgCZAGaZAJmZmZzOFZzO9ODg4P%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2FyH5BAEKAA8ALAAAAAAUABQAAAR88MlJq734qs27rwoijuSogGWBFAZ5UqGoHkdhJ%2BI7xQXtC6ocauU7CAguVNBnOwphiITtgCAUrAVBQifZ2G6ExCBh3YC8ioF1YOucO4OAxwwr0gL2A%2FehOAB8AIGAAHsKgYSGG4SIACCLioGKHBWHkYyJhpQci5GQGZ8UEQA7'
image: url + '/caption_icon.gif'
                        });

                        // Add a node change handler, selects the button in the UI when a image is selected
                        ed.onNodeChange.add(function(ed, cm, n) {
                                cm.setActive('caption', n.nodeName == 'IMG');
                        });
                },

                getInfo : function() {
                        return {
                                longname : 'Caption plugin',
                                author : 'nihki',
                                authorurl : 'http://www.madaniyah.com',
                                infourl : '',
                                version : "0.1"
                        };
                }
        });

        tinymce.PluginManager.add('caption', tinymce.plugins.CaptionPlugin);
})();