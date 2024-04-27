/*import NestedSort from 'nested-sort';

export default function nestedList({
                                       items = [],
                                       maxDepth = 2,
                                       selector = '#nestedList',
                                   }) {
    return {
        items: items,
        init() {
            let rootThis = this;
            let svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">\n' +
                '  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />\n' +
                '</svg>';
            let nestedSortable = new NestedSort({
                el: selector,
                data: rootThis.items,
                listClassNames: 'fi-nested-list',
                listItemClassNames: 'fi-nested-list-item',
                nestingLevels: maxDepth,
                propertyMap: {
                    parent: 'parent_id',
                },
                renderListItem: (el, {id, parent_id, text}) => {
                    el.innerHTML = '<div class="rounded-lg border h-10 flex w-full items-center border-gray-300 bg-white dark:border-white/10 dark:bg-gray-800">' +
                        '<div class="w-full h-full flex flex-row items-center rounded-lg px-px bg-gray-50 border-gray-300 dark:bg-gray-900 dark:border-white/10">' +
                        '<span class="text-gray-400 dark:text-gray-500">' + svg + '</span>' +
                        '<div class="gap-1 ml-4 rtl:mr-4">' + el.innerHTML + '</div>' +
                        '</div>' +
                        '</div>';
                    return el;
                },
                actions: {
                    onDrop: function (data) {
                        rootThis.items = data;
                    },
                },
            });
        },
        save() {
            this.$wire.updateNestedList(this.items)
        },
    }
}*/

import Sortable from "sortablejs"

export default function nestedList({
                                       treeId,
                                       id,
                                       maxDepth
                                   }) {
    return {
        id,
        sortable: null,

        init() {
            let el = document.getElementById(id);
            this.sortable = new Sortable(el, {
                group: treeId,
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.25,
                invertSwap: true,
                draggable: "[data-sortable-item]",
                handle: "[data-sortable-handle]",
                onMove: (evt) => {
                    if (maxDepth && maxDepth >= 0 && this.getDepth(evt.related) > maxDepth) {
                        return false;  // Prevent dragging items to a depth greater than maxDepth
                    }
                },
                onSort: () => {
                    console.log(this.sortable.toArray());
                }
            })
        },

        getDepth(el, depth = 0) {
            const parentElement = el.parentElement.closest('[data-sortable-item]');

            if (parentElement) {
                return this.getDepth(parentElement, ++depth);
            }

            return depth;
        },
    }
}
