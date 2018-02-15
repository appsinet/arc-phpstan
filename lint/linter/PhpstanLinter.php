<?php
/**
 * @copyright Copyright 2017-present Appsinet. All Rights Reserved.

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

/** Uses phpstan to lint php files */
final class PhpstanLinter extends ArcanistExternalLinter
{

    /**
     * @var string Config file path
     */
    private $configFile = null;

    /**
     * @var string Rule level
     */
    private $level = 'max';

    /**
     * @var string Autoload file path
     */
    private $autoloadFile = null;

    public function getInfoName()
    {
        return 'phpstan';
    }

    public function getInfoURI()
    {
        return '';
    }

    public function getInfoDescription()
    {
        return pht('Use phpstan for processing specified files.');
    }

    public function getLinterConfigurationName()
    {
        return 'phpstan';
    }

    public function getDefaultBinary()
    {
        return 'phpstan';
    }

    public function getInstallInstructions()
    {
        return pht('Install phpstan following the official guide at https://github.com/phpstan/phpstan#installation');
    }

    public function shouldExpectCommandErrors()
    {
        return true;
    }

    public function getVersion()
    {
        list($stdout) = execx('%C --version', $this->getExecutableCommand());

        $matches = array();
        $regex = '/(?P<version>\d+\.\d+\.\d+)/';
        if (preg_match($regex, $stdout, $matches)) {
            return $matches['version'];
        } else {
            return false;
        }
    }

    protected function getMandatoryFlags()
    {
        $flags = array(
            'analyse',
            '--no-progress',
            '--errorFormat=checkstyle'
        );
        if (null !== $this->configFile) {
            array_push($flags, '-c', $this->configFile);
        }
        if (null !== $this->level) {
            array_push($flags, '-l', $this->level);
        }
        if (null !== $this->autoloadFile) {
            array_push($flags, '-a', $this->autoloadFile);
        }

        return $flags;
    }

    public function getLinterConfigurationOptions()
    {
        $options = array(
            'config' => array(
                'type' => 'optional string',
                'help' => pht(
                    'The path to your phpstan.neon file. Will be provided as -c <path> to phpstan.'
                ),
            ),
            'level' => array(
                'type' => 'optional int',
                'help' => pht(
                    'Rule level used (0 loosest - max strictest). Will be provided as -l <level> to phpstan.'
                ),
            ),
            'autoload' => array(
                'type' => 'optional string',
                'help' => pht(
                    'The path to the auto load file. Will be provided as -a <autoload_file> to phpstan.'),
            ),
        );
        return $options + parent::getLinterConfigurationOptions();
    }

    public function setLinterConfigurationValue($key, $value)
    {
        switch ($key) {
        case 'config':
            $this->configFile = $value;
            return;
        case 'level':
            $this->level = $value;
            return;
        case 'autoload':
            $this->autoloadFile = $value;
            return;
        default:
            parent::setLinterConfigurationValue($key, $value);
            return;
        }
    }

    protected function getDefaultMessageSeverity($code)
    {
        return ArcanistLintSeverity::SEVERITY_WARNING;
    }

    protected function parseLinterOutput($path, $err, $stdout, $stderr)
    {
        $result = array();
        if (!empty($stdout)) {
            $checkstyleOutpout = new SimpleXMLElement($stdout);
            $errors = $checkstyleOutpout->xpath('//file/error');
            foreach($errors as $error) {
                    $violation = $this->parseViolation($error);
                    $violation['path'] = $path;
                    $result[] = ArcanistLintMessage::newFromDictionary($violation);
            }
        }

        return $result;
    }

    /**
     * Checkstyle returns output of the form
     *
     * <checkstyle>
     *   <file name="${sPath}">
     *     <error line="12" column="10" severity="${sSeverity}" message="${sMessage}" source="${sSource}">
     *     ...
     *   </file>
     * </checkstyle>
     *
     * Of this, we need to extract
    *   - Line
     *   - Column
     *   - Severity
     *   - Message
     *   - Source (name)
     *
     * @param SimpleXMLElement $violation The XML Entity containing the issue
     *
     * @return array of the form
     * [
     *   'line' => {int},
    *   'column' => {int},
     *   'severity' => {string},
     *   'message' => {string}
     * ]
     */
    private function parseViolation(SimpleXMLElement $violation)
    {
        return array(
            'code' => $this->getLinterName(),
            'name' => (string)$violation['message'],
            'line' => (int)$violation['line'],
            'char' => (int)$violation['column'],
            'severity' => $this->getMatchSeverity((string)$violation['severity']),
            'description' => (string)$violation['message']
        );
    }

    /**
     * @return string Linter name
     */
    public function getLinterName()
    {
        return 'phpstan';
    }

    /**
     * Map the regex matching groups to a message severity. We look for either
     * a nonempty severity name group like 'error', or a group called 'severity'
     * with a valid name.
     *
     * @param string $severity_name dict Captured groups from regex.
     *
     * @return string @{class:ArcanistLintSeverity} constant.
     *
     * @task parse
     */
    private function getMatchSeverity($severity_name)
    {
            $map = array(
                    'error' => ArcanistLintSeverity::SEVERITY_ERROR,
                    'warning' => ArcanistLintSeverity::SEVERITY_WARNING,
                    'info' => ArcanistLintSeverity::SEVERITY_ADVICE,
                );
            foreach ($map as $name => $severity) {
                    if ($severity_name == $name) {
                            return $severity;
            }
        }
        return ArcanistLintSeverity::SEVERITY_ERROR;
    }
}
