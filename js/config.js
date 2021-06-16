$(document).ready(function() {
    var $modal = $('#external-modules-configure-modal');

    // check if other modules have set this to avoid infinite redefinition loop
    if (typeof ExternalModules.Settings.prototype.resetConfigInstancesOld === 'undefined') {
        ExternalModules.Settings.prototype.resetConfigInstancesOld = ExternalModules.Settings.prototype.resetConfigInstances;
    }

    // fire on clicking "configure" for any module
    $modal.on('show.bs.modal', function() {
        ExternalModules.Settings.prototype.resetConfigInstances = function() {
            ExternalModules.Settings.prototype.resetConfigInstancesOld();

            // Making sure we are overriding this modules's modal only.
            if ($modal.data('module') !== REDCapWebServices.modulePrefix) {
                return;
            }

            // Adding "SELECT" prefix to SQL query field.
            $('[field="query_sql"]').each(function() {
                if ($(this).hasClass('select-prefix-set')) {
                    return;
                }

                $(this).children('.external-modules-input-td').prepend('<span>SELECT</span>');
                $(this).addClass('select-prefix-set');
            });

            // Adjusting columns widths to make room for the SQL query field.
            $modal.find('tr').each(function() {
                $(this).find('td').first().css('width', '50%');
            });

            ExternalModules.configsByPrefix[REDCapWebServices.modulePrefix]['system-settings'].forEach(function(setting) {
                if (setting.type === 'sub_settings') {
                    setting.sub_settings.forEach(function(subSetting) {
                        setHelper(subSetting);
                    });
                }
                else {
                    setHelper(setting);
                }
            });
        }
    });

    function setHelper(setting) {
        if (Array.isArray(setting)) {
            setting.forEach(function(instance) {
                // Call function recursively if this is a recurring setting.
                setHelper(instance);
            });

            return;
        }

        $modal.find('[field="' + setting.key + '"]').each(function() {
            if ($(this).hasClass('helper-set')) {
                return;
            }

            var $label = $(this).children('td').first();
            if ($label.find('b').length !== 0) {
                // Do not change the built in fields.
                return;
            }

            // Turning labels bold.
            $label.find('.external-modules-instance-label, label').css('font-weight', 'bold');
            if (typeof setting.helper !== 'undefined') {
                // Adding helper text, if exists on config.
                $label.find('label').after('<div class="helper"><small>' + setting.helper + '</small></div>');
            }

            $(this).addClass('helper-set');
        });
    }
});
