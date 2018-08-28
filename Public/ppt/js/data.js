// set data *
// set title *
// set design *
// add/clone/remove slide *
// move slide *
// change template *
// edit item *
// reset *

define(['storage'], function (storage) {
    var templateList = [
            {key: 'normal', title: '正常', layout: 'normal', typeMap: {title: 'text', content: 'text'}},
            {key: 'title', title: '标题', layout: 'title', typeMap: {title: 'text', content: 'text'}},
            {key: 'subtitle', title: '子标题', layout: 'subtitle', typeMap: {title: 'text', content: 'text'}},
            {key: 'double', title: '两列', layout: 'double', typeMap: {title: 'text', content: 'text', content2: 'text'}},
            {key: 'double-subtitle', title: '两列+子标题', layout: 'double-subtitle', typeMap: {title: 'text', subtitle: 'text', subtitle2: 'text', content: 'text', content2: 'text'}},
            {key: 'picture', title: '图像', layout: 'imax', typeMap: {title: 'text', content: 'img'}},
            {key: 'picture-left', title: '图像在左', layout: 'double', typeMap: {title: 'text', content: 'img', content2: 'text'}},
            {key: 'picture-right', title: '图像在右', layout: 'double', typeMap: {title: 'text', content: 'text', content2: 'img'}}
            // {key: 'video', title: 'Youku Video', layout: 'imax', typeMap: {title: 'text', content: 'video'}}
        ];
    var tmplList = [
    ];
    var layoutList = [
            {key: 'normal', title: '正常'},
            {key: 'title', title: '标题'},
            {key: 'subtitle', title: '子标题'},
            {key: 'double', title: '两列'},
            {key: 'double-subtitle', title: '两列+子标题'},
            {key: 'imax', title: 'iMax Item'}
        ];
    var typeName = {
        text: 'Text',
        img: 'Image',
        code: 'Code'
    };
    var typeMap = {
        default: {
            default: ['text', 'img', 'code'],
            title: ['text'],
            subtitle: ['text'],
            subtitle2: ['text']
        },
        title: {
            content: ['text']
        },
        subtitle: {
            content: ['text']
        }
    };
    var designList = [
            {key: 'default', title: '默认'},
            {key: 'revert', title: '反色'}
        ];
    var transitionList = [
            {key: 'horizontal', title: '正常'},
            {key: 'vertical', title: '垂直'},
            {key: 'cubic-horizontal', title: '立方体-水平'},
            {key: 'cubic-horizontal-inner', title: '立方体-嵌入'},
            {key: 'cubic-vertical', title: '立方体-垂直'},
            {key: 'cubic-vertical-inner', title: '立方体-垂直-嵌入'},
            {key: 'doors', title: '打开房门'},
            {key: 'zoom-in', title: '放大切换'},
            {key: 'zoom-out', title: '缩小切换'},
            {key: 'sublime', title: '顶点'},
            {key: 'fly', title: '飞走'},
            {key: 'fall', title: '落下'}
        ];

    var defaultData = {
        design: 'default',
        transition: 'horizontal',
        title: '',
        slides: [
            {sid: 'A', layout: 'title', items: {title: {type: 'text', value: '标题'}, content: {type: 'text', value: '请输入标题'}}},
            {sid: 'B', layout: 'normal', items: {title: {type: 'text', value: '内容'}, content: {type: 'text', value: '这里是正文...'}}},
            {sid: 'C', layout: 'imax', items: {title: {type: 'text', value: '鸣谢'}, content: {type: 'img', value: 'http://123.56.145.63:8010/Public/dist/img/jingtong2.png'}}}
            // {sid: 'D', template: 'video', layout: 'imax', items: {title: {type: 'text', value: 'Video'}, content: {type: 'video', value: 'XNjUwODE1Mg=='}}}
        ]
    };

    var data = storage.readData() || JSON.parse(JSON.stringify(defaultData));

    var onStorage = true;

    function mapToArray(obj) {
        var newObj;
        if (Object.prototype.toString.call(obj) == '[object Object]') {
            newObj = [];
            $.each(obj, function (k, v) {
                newObj.push([k, mapToArray(v)]);
            });
            newObj.sort(function (a, b) {
                return a[0] > b[0];
            });
        }
        else if (Object.prototype.toString.call(obj) == '[object Array]') {
            newObj = [];
            $.each(obj, function (i, v) {
                newObj.push(mapToArray(v));
            });
        }
        else {
            newObj = obj;
        }
        return newObj;
    }
    function checkChanged(objA, objB) {
        var newObjA = mapToArray(objA);
        var newObjB = mapToArray(objB);

        return JSON.stringify(newObjA) !== JSON.stringify(newObjB);
    }

    function extend(dest, src) {
        $.each(src, function (k, v) {
            dest[k] = v;
        });
    }

    var manager = {
        getTplList: function () {
            return templateList;
        },
        getLayoutList: function () {
            return layoutList;
        },
        getDesignList: function () {
            return designList;
        },
        getTransitionList: function () {
            return transitionList;
        },
        getTplByKey: function (key) {
            var result;
            templateList.forEach(function (tplData) {
                if (tplData.key == key) {
                    result = tplData;
                }
            });
            return result;
        },
        getDesignByKey: function (key) {
            var result;
            designList.forEach(function (designData) {
                if (designData.key == key) {
                    result = designData;
                }
            });
            return result;
        },
        getTransitionByKey: function (key) {
            var result;
            transitionList.forEach(function (transitionData) {
                if (transitionData.key == key) {
                    result = transitionData;
                }
            });
            return result;
        },
        getTypeList: function (layout, key) {
            var layoutInfo;
            var itemInfo;
            var result;

            layoutInfo = typeMap[layout];

            if (layoutInfo) {
                itemInfo = layoutInfo[key] || layoutInfo.default;
            }
            if (!itemInfo) {
                layoutInfo = typeMap.default;
                itemInfo = layoutInfo[key] || layoutInfo.default;
            }

            result = [];
            itemInfo.forEach(function (key) {
                result.push({key: key, name: typeName[key]});
            });

            return result;
        },

        getData: function () {
            return data;
        },
        getDesign: function () {
            return data.design;
        },
        getTransition: function () {
            return data.transition;
        },
        getTitle: function () {
            return data.title;
        },
        setData: function (newData) {
            data = newData;
        },
        setDesign: function (newDesign) {
            data.design = newDesign;
        },
        setTransition: function (newTransition) {
            data.transition = newTransition;
        },
        setTitle: function (newTitle) {
            data.title = newTitle;
        },

        getPageList: function () {
            var list = [];
            data.slides.forEach(function (slideData) {
                list.push({sid: slideData.sid, title: slideData.items.title.value});
            });
            return list;
        },

        getSlideList: function () {
            return data.slides;
        },
        getSlide: function (page) {
            return data.slides[page];
        },
        getSlideById: function (sid) {
            var result;
            data.slides.forEach(function (slideData) {
                if (slideData.sid == sid) {
                    result = slideData;
                }
            });
            return result;
        },
        getItem: function (page, key) {
            var slideData = data.slides[page] || {};
            var itemMap = slideData.items || {};
            var itemData = itemMap[key] || {};
            return itemData;
        },
        getValue: function (page, key) {
            var slideData = data.slides[page] || {};
            var itemMap = slideData.items || {};
            var itemData = itemMap[key] || {};
            return itemData.value;
        },

        changeLayout: function (page, layout, ignoreTypeChange) {
            var slideData = data.slides[page] || {};
            slideData.layout = layout;
        },
        // changeTemplate: function (page, template, ignoreTypeChange) {
        //     var slideData = data.slides[page] || {};
        //     var tplData = manager.getTplByKey(template);
        //     var hasNewLayout = (slideData.layout != tplData.layout);
        //     var changedKeys = [];

        //     slideData.template = template;

        //     if (hasNewLayout) {
        //         slideData.layout = tplData.layout;
        //     }

        //     $.each(tplData.typeMap, function (key, type) {
        //         var itemData = slideData.items[key];

        //         if (!itemData) {
        //             slideData.items[key] = itemData = {};
        //         }
        //         if (hasNewLayout) {
        //             itemData.position = {};
        //         }
        //         if (!itemData.value) {
        //             if (!ignoreTypeChange) {
        //                 itemData.type = type;
        //             }
        //             itemData.config = {};
        //             changedKeys.push(key);
        //         }
        //     });

        //     return changedKeys;
        // },
        changeType: function (page, key, type) {
            var slideData = data.slides[page] || {};
            var itemData = slideData.items[key];

            if (!itemData) {
                itemData = {};
                slideData.items[key] = itemData;
            }

            itemData.type = type;
            itemData.value = null;
            itemData.config = {};
        },

        clearItem: function (page, key) {
            var itemData = manager.getItem(page, key);
            itemData.value = null;
            itemData.config = {};
        },
        setValue: function (page, key, value) {
            var itemData = manager.getItem(page, key);
            itemData.value = value;
        },

        startStorage: function () {
            onStorage = true;
        },
        stopStorage: function () {
            onStorage = false;
        },

        checkItemChanged: function (page, key, outerData) {
            var itemData;

            if (!key) {
                return;
            }

            itemData = manager.getItem(page, key);

            return checkChanged(itemData, outerData);
        },

        reset: function (newData) {
            newData = newData || defaultData;
            data = JSON.parse(JSON.stringify(newData));
        },
        save: function () {
            var result;
            if (onStorage) {
                result = storage.saveData(data);
                if (result === false) {
                    console.log('storage error! (QuotaExceededError)');
                }
            }
        }
    };

    extend(manager, {
        readMedia: storage.readMedia,
        saveMedia: storage.saveMedia,
        removeMedia: storage.removeMedia,
        getMediaList: storage.getMediaList
    });

    return manager;
});