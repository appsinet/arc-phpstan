<?php
/*
 Copyright 2017-present Appsinet. All Rights Reserved.

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
     * @var int Rule level
     */
    private $level = null;

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

    public function getLinterName()
    {
        return 'phpstan';
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

    protected function getDefaultMessageSeverity($code)
    {
        return ArcanistLintSeverity::SEVERITY_WARNING;
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
            '--errorFormat=raw'
        );
        if (null !== $this->configFile) {
            array_push($flags, '-c', $this->configFile);
        }
        if (null !== $this->level) {
            array_push($flags, '-l', $this->level);
        }

        return $flags;
    }

    public function getLinterConfigurationOptions()
    {
        $options = array(
            'config' => array(
                'type' => 'optional string',
                'help' => pht(
                    'The path to your phpstan.neon file. Will be provided as -c <path> to phpstan.'),
            ),
            'level' => array(
                'type' => 'optional int',
                'help' => pht(
                    'Rule level used (0 loosest - 7 strictest). Will be provided as -l <level> to phpstan.'),
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
        default:
            parent::setLinterConfigurationValue($key, $value);
            return;
        }
    }

    protected function parseLinterOutput($path, $err, $stdout, $stderr)
    {
        $result = array();
        if (null !== $stdout && '' !== $stdout) {
            preg_match_all('/[a-zA-Z\/.]+:[0-99999]+:[a-zA-Z\/:_#\'()= \\\\$.0-99999\[\]]+/m', $stdout, $messages);
            foreach ($messages[0] as $warning) {
                $message = id(new ArcanistLintMessage())
                    ->setPath($path)
                    ->setName('phpstan violation')
                    ->setCode('phpstan')
                    ->setSeverity(ArcanistLintSeverity::SEVERITY_DISABLED);
                $fileEnd = strpos($warning, ':');
                $lineIni = strpos($warning, ':', $fileEnd) + 1;
                $lineEnd = strpos($warning, ':', $lineIni) + 1;
                $line = substr($warning, $lineIni, $lineEnd - $lineIni);
                $error = substr($warning, $lineEnd);
                $message->setSeverity(ArcanistLintSeverity::SEVERITY_ERROR);
                $message->setLine((int)$line)
                    ->setDescription("Error: $error");
                $result[] = $message;
            }
        }

        return $result;
    }
}
