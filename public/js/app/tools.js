/**
 * Created by penyaev on 09.06.14.
 */


/**
 *  Склонение слова в зависимости от числа
 *  @param {number} num  Число, для которого склоняем
 *  @param {Array} aEnds  Набор склонений в порядке: 1, 2, 5
 *  @returns {string}  Существительное в правильном склонении
 */
Pixelf.Tools.Pluralize = function (num, aEnds) {
        num = num % 100;
        if (num > 10 && num < 20) {
            return aEnds[2];
        } else {
            num = num % 10;
            switch (num) {
                case 1:
                    return aEnds[0];
                case 2:
                case 3:
                case 4:
                    return aEnds[1];
                default:
                    return aEnds[2];
            }
        }
    };